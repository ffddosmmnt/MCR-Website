param(
    [Parameter(Mandatory)][int]$HttpPort,
    [Parameter(Mandatory)][int]$FailurePort,
    [Parameter(Mandatory)][int]$ConcurrentPort,
    [Parameter(Mandatory)][scriptblock]$RunSql,
    [Parameter(Mandatory)][string]$EvidenceDirectory
)
$ErrorActionPreference = 'Stop'
$client = [Microsoft.PowerShell.Commands.WebRequestSession]::new()
function Check([bool]$Condition, [string]$Message) {
    if (-not $Condition) { throw "Dashboard test failed: $Message" }
}
function Request([string]$Path, [string]$Method = 'GET', [hashtable]$Body = @{}, [object]$Session = $client, [int]$Port = $HttpPort) {
    $args = @{ Uri="http://127.0.0.1:$Port$Path"; Method=$Method; WebSession=$Session; SkipHttpErrorCheck=$true; MaximumRedirection=0; TimeoutSec=8 }
    if ($Method -eq 'POST') { $args.Body = $Body }
    return Invoke-WebRequest @args -ErrorAction SilentlyContinue
}
function Token([object]$Response, [string]$Name = 'csrf') {
    return [regex]::Match($Response.Content, 'name="' + $Name + '" value="([^"]*)"').Groups[1].Value
}
function Sql([string]$Query) { return & $RunSql "USE mcr_foundation_test; SET time_zone = '+00:00'; $Query" }
function Form([string]$Module, [string]$Action, [int]$Id = 0) {
    $path = "/dashboard/index.php?module=$Module&action=$Action"
    if ($Id) { $path += "&id=$Id" }
    return Request $path
}
function Write-Record([string]$Module, [string]$Action, [hashtable]$Values, [int]$Id = 0, [object]$Snapshot = $null) {
    $form = if ($null -ne $Snapshot) { $Snapshot } else { Form $Module $Action $Id }
    Check ($form.StatusCode -eq 200) "Load $Module $Action form"
    $body = @{ csrf=(Token $form); version=(Token $form 'version') }
    foreach ($key in $Values.Keys) { $body[$key] = $Values[$key] }
    return Request "/dashboard/index.php?module=$Module&action=$Action&id=$Id" 'POST' $body
}

$anonymous = Request '/dashboard/'
Check ($anonymous.StatusCode -eq 200 -and $anonymous.Content -match 'Administrator') 'Direct dashboard access without an account'
Check ($anonymous.Content -notmatch 'Sign out|Sign in|logout.php|login.php') 'No login/logout controls'
foreach ($path in @('/dashboard/login.php','/dashboard/logout.php','/dashboard/internal/create-admin.php','/dashboard/internal/admin.php','/dashboard/internal/view.php','/dashboard/internal/test.ps1','/dashboard/.htaccess')) {
    Check ((Request $path).StatusCode -eq 404) "Private/removed path: $path"
}
foreach ($path in @('/dashboard/adminlte.min.css','/dashboard/template.css','/dashboard/dashboard.js')) {
    Check ((Request $path).StatusCode -eq 200) "Dashboard asset $path"
}
Check ((Request '/dashboard/index.php?module=albums&action=create' 'POST' @{title='Missing CSRF'}).StatusCode -eq 403) 'CSRF still required for direct access'
$fixtures = [ordered]@{
    band_members = @{name='CRUD Member <script>alert(1)</script>'; instrument='Guitar'; cover_img=''}
    albums = @{title='CRUD Album'; release_date='2026-11-12'; cover_image=''}
    songs = @{album_id=''; title='CRUD Song'; lyrics="Line one`n<script>alert(1)</script>"}
    news_articles = @{title='CRUD News'; slug='crud-test-news'; content='Body <img src=x onerror=alert(1)>'; news_date='2026-11-12T12:00'}
    venues = @{name='CRUD Venue'; city='Jakarta'; country='Indonesia'}
    concerts = @{venue_id=''; concert_date='2026-11-17T20:00'; status='scheduled'}
    ticket_types = @{concert_id=''; name='CRUD Ticket'; price='125.50'; quota='20'}
    categories = @{name='CRUD Category'}
    products = @{category_id=''; name='CRUD Product'; price='19.99'; details='Dummy details'}
    product_variants = @{product_id=''; size='M'; color='Black'; stock='5'}
    users = @{name='CRUD User'; email='crud-user@example.test'; password_hash='Dummy-user-pass-2026'; role='user'}
    merch_orders = @{user_id=''; status='pending'; total_amount='999.99'}
    merch_order_items = @{order_id=''; variant_id=''; quantity='2'; unit_price='19.99'; subtotal='0.01'; address='Dummy test address'}
    ticket_orders = @{user_id=''; status='pending'; total_amount='999.99'}
    ticket_order_items = @{order_id=''; ticket_type_id=''; quantity='2'; unit_price='125.50'; subtotal='0.01'; order_code='12345'}
}
$ids = @{}
$parentMap = @{
    songs=@{album_id='albums'}; concerts=@{venue_id='venues'}; ticket_types=@{concert_id='concerts'}
    products=@{category_id='categories'}; product_variants=@{product_id='products'}
    merch_orders=@{user_id='users'}; ticket_orders=@{user_id='users'}
    merch_order_items=@{order_id='merch_orders'; variant_id='product_variants'}
    ticket_order_items=@{order_id='ticket_orders'; ticket_type_id='ticket_types'}
}
$schemaTables = @((Sql 'SHOW TABLES;').Trim() -split '\r?\n')
Check ((($schemaTables | Sort-Object) -join ',') -eq (($fixtures.Keys | Sort-Object) -join ',')) 'Fixtures cover every schema table'
foreach ($module in $fixtures.Keys) {
    if ($parentMap.ContainsKey($module)) {
        foreach ($key in $parentMap[$module].Keys) { $fixtures[$module][$key] = [string]$ids[$parentMap[$module][$key]] }
    }
    $created = Write-Record $module 'create' $fixtures[$module]
    Check ($created.StatusCode -eq 303) "Create $module (got $($created.StatusCode))"
    $ids[$module] = [int](Sql "SELECT MAX(id) FROM $module;")
    $listed = Request "/dashboard/index.php?module=$module"
    Check ($listed.StatusCode -eq 200 -and $listed.Content -match "action=edit(?:&amp;|&)id=$($ids[$module])") "Read $module"
}
Write-Output 'PASS: WBS 2.2 Create/Read for all 15 database tables.'
$initialPasswordHash = (Sql "SELECT password_hash FROM users WHERE id=$($ids.users);").Trim()
Check ($initialPasswordHash -match '^\$2y\$' -and $initialPasswordHash -ne $fixtures.users.password_hash) 'Password stored as a hash'
$usersPage = Request '/dashboard/index.php?module=users'
Check ($usersPage.Content -notmatch '\$2y\$|Dummy-user-pass-2026') 'Passwords and hashes excluded from user list'
$userEditPage = Form 'users' 'edit' $ids.users
Check ($userEditPage.Content -notmatch '\$2y\$|Dummy-user-pass-2026') 'Password input is empty when editing'
foreach ($module in $fixtures.Keys) {
    $changedKey = if ($fixtures[$module].ContainsKey('title')) { 'title' } elseif ($fixtures[$module].ContainsKey('name')) { 'name' } elseif ($fixtures[$module].ContainsKey('quantity')) { 'quantity' } elseif ($fixtures[$module].ContainsKey('status')) { 'status' } else { 'stock' }
    $fixtures[$module][$changedKey] = switch ($changedKey) { 'stock' { '6' }; 'quantity' { '3' }; default { $fixtures[$module][$changedKey] + ' updated' } }
    if ($module -eq 'users') { $fixtures.users.password_hash = '' }
    $edited = Write-Record $module 'edit' $fixtures[$module] $ids[$module]
    Check ($edited.StatusCode -eq 303) "Update $module"
    Check ((Sql "SELECT $changedKey FROM $module WHERE id=$($ids[$module]);").Trim() -eq $fixtures[$module][$changedKey]) "Persist updated $module"
}
Check ((Sql "SELECT password_hash FROM users WHERE id=$($ids.users);").Trim() -eq $initialPasswordHash) 'Blank edit password preserves existing password'
foreach ($family in @(@('merch_orders','merch_order_items','59.97'), @('ticket_orders','ticket_order_items','376.50'))) {
    Check ((Sql "SELECT total_amount FROM $($family[0]) WHERE id=$($ids[$family[0]]);").Trim() -eq $family[2]) 'Server-derived order total'
    Check ((Sql "SELECT subtotal FROM $($family[1]) WHERE id=$($ids[$family[1]]);").Trim() -eq $family[2]) 'Server-derived subtotal ignores supplied value'
}
Check ((Write-Record 'users' 'create' @{name='Duplicate'; email='crud-user@example.test'; password_hash='Another-dummy-pass-2026'; role='user'}).StatusCode -eq 409) 'Duplicate email'
Check ((Write-Record 'users' 'create' @{name='Invalid'; email='bad-email'; password_hash='Another-dummy-pass-2026'; role='user'}).StatusCode -eq 422) 'Invalid email'
$weakPassword = Write-Record 'users' 'create' @{name='Invalid'; email='invalid@example.test'; password_hash='short'; role='user'}
Check ($weakPassword.StatusCode -eq 422 -and $weakPassword.Content -notmatch 'value="short"') 'Weak password rejected without echoing it'
Check ((Write-Record 'users' 'edit' @{name='Invalid'; email='crud-user@example.test'; password_hash=''; role='owner'} $ids.users).StatusCode -eq 422) 'Invalid role'
Check ((Request '/dashboard/index.php?module=users&sort=password_hash').StatusCode -eq 400) 'Password hash cannot be sorted/searched'
Check ((Write-Record 'users' 'delete' @{} $ids.users).StatusCode -eq 409) 'Referenced user cannot be deleted'

# Keep the final administrator available for the preserved login dashboard.
$guardAccount = @{name='Guard admin'; email='guard-admin@example.test'; password_hash='Guard-dummy-pass-2026'; role='admin'}
Check ((Write-Record 'users' 'create' $guardAccount).StatusCode -eq 303) 'Create admin through Users'
$guardId = [int](Sql 'SELECT MAX(id) FROM users;')
Check ((Write-Record 'users' 'delete' @{} $guardId).StatusCode -eq 409) 'Last admin cannot be deleted'
$guardAccount.role = 'user'; $guardAccount.password_hash = ''
Check ((Write-Record 'users' 'edit' $guardAccount $guardId).StatusCode -eq 409) 'Last admin cannot be downgraded'
$secondAdmin = @{name='Second admin'; email='second-admin@example.test'; password_hash='Initial-dummy-pass-2026'; role='admin'}
Check ((Write-Record 'users' 'create' $secondAdmin).StatusCode -eq 303) 'Create another administrator'
$secondAdminId = [int](Sql 'SELECT MAX(id) FROM users;')
$secondAdmin.password_hash = 'Changed-dummy-pass-2026'
Check ((Write-Record 'users' 'edit' $secondAdmin $secondAdminId).StatusCode -eq 303) 'Change password through Users'
$authClient = [Microsoft.PowerShell.Commands.WebRequestSession]::new()
$authLogin = Request '/dashboard-auth/login.php' 'GET' @{} $authClient
$authResult = Request '/dashboard-auth/login.php' 'POST' @{csrf=(Token $authLogin); email=$secondAdmin.email; password=$secondAdmin.password_hash} $authClient
Check ($authResult.StatusCode -eq 303) 'Changed password works in the preserved login dashboard'
Check ((Write-Record 'users' 'delete' @{} $secondAdminId).StatusCode -eq 303) 'Delete administrator when another remains'
$null = Sql "DELETE FROM users WHERE id=$guardId;"

foreach ($family in @(@('merch_orders','merch_order_items','variant_id','product_variants','59.97'), @('ticket_orders','ticket_order_items','ticket_type_id','ticket_types','376.50'))) {
    $orderTable, $itemTable, $referenceKey, $referenceTable, $expectedAmount = $family
    Check ((Write-Record $orderTable 'delete' @{} $ids[$orderTable]).StatusCode -eq 409) 'Order with items cannot be deleted'
    Check ((Write-Record $orderTable 'create' @{user_id=[string]$ids.users; status='pending'; total_amount='999.99'}).StatusCode -eq 303) 'Create another order'
    $secondOrderId = [int](Sql "SELECT MAX(id) FROM $orderTable;")
    Check ((Sql "SELECT total_amount FROM $orderTable WHERE id=$secondOrderId;").Trim() -eq '0.00') 'Empty order total is zero'
    $moved = $fixtures[$itemTable].Clone(); $moved.order_id = [string]$secondOrderId
    Check ((Write-Record $itemTable 'edit' $moved $ids[$itemTable]).StatusCode -eq 303) 'Move item between orders'
    Check ((Sql "SELECT total_amount FROM $orderTable WHERE id=$($ids[$orderTable]);").Trim() -eq '0.00') 'Source total recalculated after move'
    Check ((Sql "SELECT total_amount FROM $orderTable WHERE id=$secondOrderId;").Trim() -eq $expectedAmount) 'Destination total recalculated after move'
    Check ((Write-Record $itemTable 'edit' $fixtures[$itemTable] $ids[$itemTable]).StatusCode -eq 303) 'Move item back'
    $tooLarge = $fixtures[$itemTable].Clone(); $tooLarge.unit_price = '9999999999.99'; $tooLarge.quantity = '2'
    Check ((Write-Record $itemTable 'create' $tooLarge).StatusCode -eq 422) 'Subtotal overflow rejected'
    $tooLarge.quantity = '1'
    Check ((Write-Record $itemTable 'create' $tooLarge).StatusCode -eq 422) 'Aggregate total overflow rolls back item insert'
    Check ((Sql "SELECT COUNT(*) FROM $itemTable WHERE order_id=$($ids[$orderTable]);").Trim() -eq '1') 'Overflow leaves no partial item'
    Check ((Sql "SELECT total_amount FROM $orderTable WHERE id=$($ids[$orderTable]);").Trim() -eq $expectedAmount) 'Overflow preserves total'
    $zeroQuantity = $fixtures[$itemTable].Clone(); $zeroQuantity.quantity = '0'
    Check ((Write-Record $itemTable 'create' $zeroQuantity).StatusCode -eq 422) 'Zero quantity rejected'

    $oldHeader = Form $orderTable 'edit' $secondOrderId
    $triggerName = 'test_' + $itemTable + '_delay'
    $null = Sql "CREATE TRIGGER $triggerName BEFORE INSERT ON $itemTable FOR EACH ROW DO SLEEP(0.2);"
    $clients = @(); $pendingRequests = @(); $requestBodies = @()
    try {
        $ports = @($HttpPort, $ConcurrentPort)
        for ($index = 0; $index -lt 2; $index++) {
            $handler = [Net.Http.HttpClientHandler]::new()
            $handler.AllowAutoRedirect = $false
            $handler.CookieContainer = [Net.CookieContainer]::new()
            $httpClient = [Net.Http.HttpClient]::new($handler)
            $clients += $httpClient
            $requestUrl = "http://127.0.0.1:$($ports[$index])/dashboard/index.php?module=$itemTable&action=create"
            $html = $httpClient.GetStringAsync($requestUrl).GetAwaiter().GetResult()
            $parameters = [Collections.Generic.Dictionary[string,string]]::new()
            foreach ($key in $fixtures[$itemTable].Keys) { $parameters[$key] = [string]$fixtures[$itemTable][$key] }
            $parameters['csrf'] = [regex]::Match($html, 'name="csrf" value="([^"]+)"').Groups[1].Value
            $parameters['order_id'] = [string]$secondOrderId
            $parameters['quantity'] = '1'
            $parameters['unit_price'] = if ($index -eq 0) { '0.10' } else { '0.20' }
            $content = [Net.Http.FormUrlEncodedContent]::new($parameters)
            $requestBodies += $content
            $pendingRequests += $httpClient.PostAsync($requestUrl, $content)
        }
        foreach ($task in $pendingRequests) {
            $response = $task.GetAwaiter().GetResult()
            Check ([int]$response.StatusCode -eq 303) 'Concurrent item insert succeeds'
            $response.Dispose()
        }
    } finally {
        foreach ($content in $requestBodies) { $content.Dispose() }
        foreach ($httpClient in $clients) { $httpClient.Dispose() }
        $null = Sql "DROP TRIGGER IF EXISTS $triggerName;"
    }
    Check ((Sql "SELECT total_amount FROM $orderTable WHERE id=$secondOrderId;").Trim() -eq '0.30') 'Concurrent edits keep the exact total'
    $staleHeader = @{user_id=[string]$ids.users; status='changed from stale form'}
    Check ((Write-Record $orderTable 'edit' $staleHeader $secondOrderId $oldHeader).StatusCode -eq 409) 'Item changes invalidate a stale order form'
    $filtered = Request "/dashboard/index.php?module=$itemTable&filter_order_id=$secondOrderId&filter_$referenceKey=$($ids[$referenceTable])"
    Check ($filtered.StatusCode -eq 200 -and $filtered.Content -match 'Showing 1–2 of 2 records') 'Both item relations can be filtered together'
    $extraIds = (Sql "SELECT id FROM $itemTable WHERE order_id=$secondOrderId;").Trim() -split '\r?\n'
    foreach ($extraId in $extraIds) { Check ((Write-Record $itemTable 'delete' @{} ([int]$extraId)).StatusCode -eq 303) 'Delete extra item' }
    Check ((Sql "SELECT total_amount FROM $orderTable WHERE id=$secondOrderId;").Trim() -eq '0.00') 'Deleting all items restores zero total'
    Check ((Write-Record $orderTable 'delete' @{} $secondOrderId).StatusCode -eq 303) 'Delete empty extra order'
}
Check ((Sql "SELECT stock FROM product_variants WHERE id=$($ids.product_variants);").Trim() -eq '6') 'Administrative orders leave stock unchanged'
Check ((Sql "SELECT quota FROM ticket_types WHERE id=$($ids.ticket_types);").Trim() -eq '20') 'Administrative orders leave quota unchanged'
Write-Output 'PASS: administrative order totals, moves, overflow rollback, two-worker concurrency and unchanged stock/quota.'
Check ((Sql "SELECT concert_date FROM concerts WHERE id=$($ids.concerts);").Trim() -eq '2026-11-17 13:00:00') 'WIB stored in UTC'
Check ((Request '/api/tour.php').Content -match '17 Nov 2026, 20:00 WIB') 'Concert frontend integration'
Check ((Request '/api/music.php').Content -match 'CRUD Album' -and (Request '/api/music.php').Content -match 'CRUD Song') 'Music frontend integration'
Check ((Request '/api/article.php?slug=crud-test-news').Content -match 'CRUD News') 'News frontend integration'
Check ((Request '/dashboard/index.php?module=band_members').Content -match '&lt;script&gt;' -and (Request '/dashboard/index.php?module=band_members').Content -notmatch '<script>alert') 'Escaped output'
Check ((Write-Record 'news_articles' 'create' $fixtures.news_articles).StatusCode -eq 409) 'Duplicate slug'
Check ((Write-Record 'albums' 'delete' @{} $ids.albums).StatusCode -eq 409) 'RESTRICT parent deletion'
Check ((Write-Record 'songs' 'create' @{album_id='4294967295'; title='Invalid'; lyrics=''}).StatusCode -eq 404) 'Unknown parent'
Check ((Write-Record 'products' 'create' @{category_id=[string]$ids.categories; name='Bad price'; price='-1'; details=''}).StatusCode -eq 422) 'Negative price'
Check ((Write-Record 'product_variants' 'create' @{product_id=[string]$ids.products; size=''; color=''; stock='2147483648'}).StatusCode -eq 422) 'Stock overflow'
Check ((Write-Record 'albums' 'create' @{title='Bad date'; release_date='2026-02-30'; cover_image=''}).StatusCode -eq 422) 'Invalid calendar date'
Check ((Write-Record 'albums' 'create' @{title='Bad image'; release_date=''; cover_image='javascript:alert(1)'}).StatusCode -eq 422) 'Unsafe image'
Check ((Write-Record 'albums' 'create' @{title=''; release_date=''; cover_image=''}).StatusCode -eq 422) 'Required field'
$stale = Form 'albums' 'edit' $ids.albums
$null = Sql "UPDATE albums SET title='Concurrent change' WHERE id=$($ids.albums);"
Check ((Write-Record 'albums' 'edit' $fixtures.albums $ids.albums $stale).StatusCode -eq 409) 'Stale edit'
$staleDelete = Form 'band_members' 'delete' $ids.band_members
$null = Sql "UPDATE band_members SET instrument='Bass' WHERE id=$($ids.band_members);"
Check ((Write-Record 'band_members' 'delete' @{} $ids.band_members $staleDelete).StatusCode -eq 409) 'Stale delete'
Check ((Request '/dashboard/index.php?module=albums&search=no-such-record').Content -match 'No records found') 'Empty search'
Check ((Request '/dashboard/index.php?module=albums&search=%27%20OR%201%3D1--').Content -match 'No records found') 'SQL-like search remains literal'
foreach ($path in @('/dashboard/index.php?module=unknown','/dashboard/index.php?module=albums;DROP','/dashboard/index.php?module=albums&sort=unknown','/dashboard/index.php?module=albums&search[]=x','/dashboard/index.php?module=albums&search=%FF','/dashboard/index.php?module=albums&action=edit&id=-1')) {
    Check ((Request $path).StatusCode -in @(400,404)) "Invalid input $path"
}
Check ((Request '/dashboard/index.php?module=songs&filter=1').StatusCode -eq 200) 'Relation filter'
Check ((Request '/dashboard/index.php?module=albums&limit=5&page=9999&sort=title&direction=asc').StatusCode -eq 200) 'Page clamp and sort'
$null = Sql "INSERT INTO categories (name) VALUES ('Page fixture 1'),('Page fixture 2'),('Page fixture 3'),('Page fixture 4'),('Page fixture 5'),('Page fixture 6'),('Page fixture 7');"
$secondPage = Request '/dashboard/index.php?module=categories&limit=5&page=2&sort=name&direction=asc'
Check ($secondPage.StatusCode -eq 200 -and $secondPage.Content -match 'Showing 6–8 of 8 records' -and $secondPage.Content -match 'Page 2 of 2') 'Second page with more than five records'
$null = Sql "DELETE FROM categories WHERE name LIKE 'Page fixture %';"
Check ((Request '/dashboard/index.php' 'PUT').StatusCode -eq 405) 'Write method restriction'
$failureSession = [Microsoft.PowerShell.Commands.WebRequestSession]::new()
$failedDashboard = Request '/dashboard/' 'GET' @{} $failureSession $FailurePort
Check ($failedDashboard.StatusCode -eq 503 -and $failedDashboard.Content -notmatch 'SQLSTATE|PDOException|Stack trace|mcr_reader') 'Safe database failure'
foreach ($module in @('ticket_order_items','ticket_orders','merch_order_items','merch_orders','users','product_variants','products','categories','ticket_types','concerts','venues','news_articles','songs','albums','band_members')) {
    Check ((Write-Record $module 'delete' @{} $ids[$module]).StatusCode -eq 303) "Delete $module"
    Check ((Sql "SELECT COUNT(*) FROM $module WHERE id=$($ids[$module]);").Trim() -eq '0') "Verify deleted $module"
}
$evidence = @{modules=@($fixtures.Keys); wbs_2_2='create/read passed for all 15 tables'; wbs_2_3='update/delete passed for all 15 tables'; access='direct admin without accounts/login/logout; CSRF retained'; relations='RESTRICT, nonexistent parent'; validation='dates, money, stock, images, required, UTF-8, arrays, users'; concurrency='stale forms rejected; two PHP workers preserve exact order totals'; orders='derived totals, item moves, overflow rollback; stock/quota unchanged'; users='hashed passwords, blank preservation, password change login, unique email, last admin protection'; frontend='Music, News, Tour share persisted records'; database='isolated mcr_foundation_test only'}
[IO.File]::WriteAllText((Join-Path $EvidenceDirectory 'dashboard-evidence.json'), ($evidence | ConvertTo-Json -Depth 4))
Write-Output 'PASS: WBS 2.3 Update/Delete for all 15 tables; users, totals, CSRF, relations, conflicts and frontend integration.'

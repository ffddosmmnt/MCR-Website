# MCR Website

A multi-page fan website for **My Chemical Romance**, built from editorial wireframes.

Website URL: https://ffddosmmnt.github.io/MCR-Website/

## Stack

- HTML
- CSS (Arial/Helvetica/system fonts)
- Vanilla JavaScript (no frameworks, no dependencies)
- Native PHP with PDO MySQL, and MySQL

## Structure

The project uses shared `base.css` and `script.js` files, plus a dedicated HTML and CSS file for each page.

## Sections

1. **Home** — Hero, upcoming shows preview, news, and featured stream
2. **About** — Band history, members, timeline, quotes, and a private contact form
3. **Music** — Featured release, discography, searchable lyrics, and fan favorites
4. **Tour** — Database concert schedule in WIB, venues, and ticket selection
5. **Store** — Merchandise showcase, product filtering, lookbook, and fan bag summary
6. **News** — News updates, article detail, newsletter registration, and selected fan messages
7. **Checkout** — Ticket/merchandise review, merchandise address, and pending order confirmation
8. **Fan Account** — Registration, sign-in, and sign-out

## Features

- Accessible responsive navigation with mobile menu, Escape handling, and reduced-motion support
- Live song and album filtering with result count and empty state
- Responsive YouTube embed with an external viewing link; playback depends on the provider
- Interactive ticket quantity with minimum/maximum limits
- Server-calculated checkout totals with no additional fees
- Merchandise filtering with an interactive fan bag total

## Setup and running

Requirements: Laragon with PHP (PDO MySQL and mbstring), MySQL, and Windows PowerShell 5.1 or newer. The GD extension is required for dashboard image uploads.

1. Open Laragon and start **MySQL**. Apache is not required for this method.
2. For a new device/database, import the SQL files through HeidiSQL or phpMyAdmin in this order:
   - `mcr_database_mysql.db` — an SQL file that creates the `mcr_website` database.
   - Select `mcr_website`, then import `migrations/001_checkout_requests.sql`.
   - Import `migrations/002_interactions.sql` into the same database.
   - Import `migrations/003_product_images.sql` to add product images.
   - Import `database/content.sql` to populate the initial content. Stop if an error occurs; do not commit a failed transaction.

   Existing complete installations do not need to reimport the schema. See [database/README.md](database/README.md) for the full import guide.
3. Open Windows PowerShell in the project folder. For the current project location:

   ```powershell
   cd "D:\Kuliah\Semester 5\WebProgramming\Kelompok"
   powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\start-local.ps1
   ```

4. If prompted, enter your local MySQL username and password. For an empty password, press Enter. The script uses `DB_USER`/`DB_PASSWORD` from the terminal environment when available, otherwise prompts for them; credentials are not saved to files.
5. Open [http://127.0.0.1:8000/](http://127.0.0.1:8000/) and check News and Tour. Keep the terminal open while using the website.
6. Press **Ctrl+C** in the terminal to stop the website.

If port 8000 is already in use, run:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\start-local.ps1 -Port 8001
```

Then open [http://127.0.0.1:8001/](http://127.0.0.1:8001/). If PHP is not detected, add `-Php "path/to/php.exe"`.

The script serves the project folder containing `start-local.ps1`, rather than a copy in `laragon/www`. Neither `pwsh` nor PowerShell 7 is required. `ExecutionPolicy Bypass` applies only to this process and does not change the saved Windows policy. Files such as `.env` are not loaded automatically; use `DB_HOST`, `DB_PORT`, and `DB_NAME` if your database settings differ from the local defaults.

GitHub Pages or a static server only serves the frontend; PHP APIs, accounts, and orders require a PHP/MySQL backend.

## PHP/MySQL catalog and checkout

Home, Music, News, Tour, and Store load database content through native PHP JSON APIs. Store displays four products with photos; administrators can replace photos under Products. Fan accounts and ticket/merchandise checkout use PHP sessions on the backend host. Orders use IDR with no extra fees and at most two tickets per account per concert across orders. Payment processing is not integrated. Creating a `pending` order reduces stock/quota; cancellation is not available.

For a new installation, follow the schema and content import order above, including all three migrations. Migration `001_checkout_requests.sql` adds the request-key table used to prevent duplicate orders. Existing installations need only migrations that have not yet been applied. Configure `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` in the PHP process environment; `.env` files are not loaded automatically. The backend needs PDO MySQL and mbstring. Run from the project root after setting those variables:

```powershell
php -d display_errors=0 -S 127.0.0.1:8000 router.php
```

Open `http://127.0.0.1:8000/showcase.html`, or create a fan account at `http://127.0.0.1:8000/account.html`. Select merchandise variants or choose a concert from Tour, review the server total, and place an order. Checkout does not collect card details. After a successful order, the confirmation shows item quantities, unit prices, total, the actual order status and a thank-you message. Use **Print / Save PDF** to open the browser print dialog; printing or reopening the confirmation does not create another order.

GitHub Pages serves the HTML frontend only. Set the public HTTPS API directory in `site-config.js` for hosted database content. With separate frontend/backend hosts, account and checkout pages offer **Continue to checkout**, where the session remains on one origin. The backend must also serve those HTML pages and their assets. Keep database credentials out of JavaScript. The administrator dashboard now requires an admin login; validate HTTPS and server file protection on the deployment host.

## Contact, newsletter, and fan messages

After selecting the application database, import `migrations/002_interactions.sql`. It adds private contacts, newsletter subscriptions, and published fan messages to the same database. Existing tables are preserved.

Visitors can send a private message from About and join the newsletter list on News or article detail. Newsletter registration records the subscription; email delivery is not connected. Fan messages are selected manually by the administrator from external email, then entered through the **Fan messages (published)** dashboard module. Saving publishes the public display name and message on News.

Private contacts and subscriber lists require administrator sign-in at `/dashboard-auth/messages.php`; they are not exposed by public content APIs. The primary dashboard at `/dashboard/` also requires admin sign-in and shares that administrator session. Include that authenticated dashboard deliberately when preparing deployment; the current local Git ignore settings exclude that directory. No credentials belong in the frontend configuration.

## Authentication and access

Public registration creates a `user` account only. The Fan Account page displays the current role from the server; an administrator can open the dashboard link. Sign in once with an existing admin account through Fan Account or `/dashboard/login.php`. That session opens both dashboard addresses and the private inbox without a second login. Signing out from the website or dashboard signs out the account everywhere on that backend host.

Roles and password versions are checked again on requests. Anonymous visitors cannot open or submit dashboard forms, ordinary accounts cannot sign in as administrators, and orders remain restricted to their owner. Administrator sessions expire after 30 minutes of inactivity. No additional database migration is required for authentication/access changes.

## Database content for another device

The repository includes a portable content pack with four albums, 20 selected songs, six editorial articles plus three sourced news summaries, a merchandise catalog, concert/ticket records and published fan messages. Event schedules, merchandise, prices, inventory and fan messages are fictional coursework content; they are not official band information. No accounts, credentials, orders or private contacts are exported.

Follow [database/README.md](database/README.md) to import the existing schema and migrations, configure DB_*, then run `php database/import.php --dry-run` and `php database/import.php`. Repeated imports retain matching records and do not reset stock. Copy assets/ with the code. The guide lists temporary artwork and the final content/images still needed.
# Aturan kerja proyek MCR

Aturan ini berlaku untuk seluruh folder proyek dan menjadi panduan bagi AI serta kontributor. Gunakan Bahasa Indonesia untuk komunikasi dan dokumentasi kerja; pertahankan bahasa antarmuka yang ada kecuali diminta mengubahnya.

Dokumen kerja agent dipusatkan di `agent/`; [AGENTS.md di root](../AGENTS.md) menjadi pintu masuk. Jalur kode dalam inline code pada dokumen ini mengacu pada root repositori, sedangkan tautan Markdown mengacu pada lokasi dokumen. Simpan panduan/rencana/catatan agent berikutnya di folder ini dan perbarui sumber yang sudah ada, bukan membuat salinan aturan atau checklist baru.

## Konteks dan sumber kebenaran

- Proyek kelompok mata kuliah Web Programming: pengembangan website My Chemical Romance dengan PHP native sebagai backend dan MySQL sebagai database.
- Abyan adalah pengembang backend. Jadwal juga mencantumkan Raffa Muhammad Shaddam sebagai pengembang backend; jangan menganggap seluruh modul backend menjadi tugas Abyan.
- Baca [PROJECT_CONTEXT.md](PROJECT_CONTEXT.md) untuk fakta proyek, jadwal, batas informasi, dan kebutuhan input.
- Gunakan [BACKEND_TASKS.md](BACKEND_TASKS.md) sebagai peta pekerjaan backend, [agent/plan.md](plan.md) sebagai rencana WBS 3.1–3.5, dan [agent/todo.md](todo.md) sebagai checklist eksekusinya. Dokumen rencana bukan bukti fitur telah diimplementasikan.
- Utamakan instruksi eksplisit pengguna, kebutuhan tugas yang terkonfirmasi, skema database milik tim, lalu implementasi aktual. Jika sumber bertentangan dan memengaruhi hasil, jelaskan konflik dan minta keputusan.
- Sebutan “official web” adalah tujuan/nama proyek akademik, bukan bukti afiliasi atau persetujuan resmi dari band. Jangan membuat klaim afiliasi atau lisensi aset tanpa sumber.

## Kejujuran dan klarifikasi

- Jangan mengarang fitur wajib, aturan dosen, anggota tim, pembagian tugas, tenggat, tabel, kolom, kredensial, endpoint, atau status pekerjaan.
- Bedakan fakta terverifikasi, inferensi, asumsi sementara, dan hal yang belum diketahui. Gunakan sumber primer jika klaim memerlukan verifikasi.
- Periksa kode dan dokumentasi sebelum meminta informasi. Jangan meminta ulang informasi yang sudah tersedia.
- Jika informasi yang belum tersedia menentukan kontrak data, keamanan, atau perilaku bisnis, tanyakan input yang spesifik beserta alasan dibutuhkannya. Lanjutkan bagian pekerjaan yang tidak bergantung pada jawaban itu.
- Untuk pilihan implementasi kecil yang mudah dibalik, gunakan pola proyek yang ada dan jelaskan asumsi yang relevan. Jangan mengubah asumsi menjadi persyaratan tim.
- Jangan menyatakan pengujian, akses sumber, integrasi, penyimpanan data, atau deployment berhasil tanpa bukti pelaksanaan.
- Status pada jadwal adalah laporan tim pada saat dibaca, bukan bukti bahwa implementasi tersedia di checkout ini.

## Kondisi repositori dan batas perubahan

- Saat inisialisasi aturan, implementasi berupa HTML/CSS/JavaScript statis. WBS 3.1 menambahkan koneksi PDO/API album, dan WBS 3.2 membuat Home, Music, News/detail, serta Tour dinamis. About, Store, dan checkout masih prototipe HTML. Skema MySQL tersedia dalam [mcr_database_mysql.db](../mcr_database_mysql.db), berupa teks SQL meskipun berekstensi `.db`; bukan SQLite.
- Keputusan Abyan pada 3.2: Shared Playlist adalah daftar lagu bersama dari `songs`, bukan playlist pribadi/kurasi baru; waktu konser memakai WIB (`Asia/Jakarta`). Jangan meminta ulang keputusan tersebut. Lihat [REPORT_3_2.md](REPORT_3_2.md) dan [TESTING_3_2.md](TESTING_3_2.md).
- Query konten bersama berada di `app/content.php`; helper dashboard/gambar/tanggal di `app/view.php`, kontrak JSON di `app/public-api.php`, dan render publik di `content.js`/template HTML. `app/views/` sudah tidak digunakan. Gunakan ulang pemilik perilaku ini. Daftar konten dibatasi 100, ringkasan Home tiga; jangan mengklaim seluruh record di atas batas itu ditampilkan. Database session membaca TIMESTAMP dalam UTC, lalu UI mengonversi konser ke WIB.
- Baca [FOUNDATION_SETUP.md](FOUNDATION_SETUP.md) untuk environment `DB_*`, server development, kontrak GET album, perlindungan file, dan perintah uji. [REPORT_3_1.md](REPORT_3_1.md) mencatat verifikasi lokal yang telah dilakukan. Gunakan ulang `database()`; jangan membuat koneksi/config tandingan.
- Server development wajib dijalankan dengan `router.php` dan display_errors nonaktif; router mengizinkan route/halaman/aset tertentu saja. Tambahkan route baru secara eksplisit. `.htaccess` disediakan untuk Apache, tetapi memerlukan mod_rewrite/AllowOverride dan belum diuji pada virtual host; verifikasi akses berkas privat sebelum beralih server.
- Instruksi pengguna Tugas #5 dan pembaruan pemisahan versi: `dashboard/` langsung membuka Administrator tanpa akun/login/logout; `dashboard-auth/` adalah salinan versi sebelumnya yang tetap memerlukan login `users.role=admin`. Penghapusan autentikasi pada versi langsung diminta eksplisit pengguna, bukan pola untuk endpoint lain. Seluruh kode khusus masing-masing versi tetap di foldernya; jangan menaruhnya di halaman utama atau `app/`. Gunakan koneksi bersama `app/database.php`, CSRF, validasi, dan snapshot konflik pada kedua versi. Blokir akses web `internal/` (termasuk sesi) pada kedua folder; jalur PHP publik versi langsung hanya index, versi auth index/login/logout. Kontrak/setup ada di [FOUNDATION_SETUP.md](FOUNDATION_SETUP.md). Pembaruan tugas WBS 2.2/2.3 memperluas versi aktif menjadi CRUD seluruh 15 tabel; salinan auth tetap 10 tabel. CSS AdminLTE berasal dari template yang diberikan.
- Keputusan pengguna untuk CRUD pesanan: **pencatatan administrasi**, subtotal/total otomatis dan stok/kuota tetap dikelola terpisah. Helper `dashboard/internal/orders.php` memiliki perhitungan uang integer cents, row lock induk sebelum item, serta pembaruan total dalam transaksi yang sama untuk create/edit/delete/pindah item. Harga satuan adalah nilai catatan admin, bukan pembayaran atau booking publik. Total/subtotal dan waktu otomatis tidak menerima nilai form sebagai sumber kebenaran. Users memakai hash password, hash tidak ditampilkan/dicari/diurutkan, password kosong pada edit dipertahankan, dan admin terakhir tidak boleh dihapus/diturunkan. Jangan menyatakan pengumpulan GitHub/Deliverable selesai berdasarkan tes lokal.
- Upload pada versi aktif `dashboard/`: anggota band dan album memakai pemilih file, bukan input path. Helper upload dimiliki `dashboard/internal/images.php`; penyimpanan `dashboard/uploads/` memakai nama acak JPG/PNG/WebP, validasi MIME/piksel, batas input 2 MB, sisi 6000 piksel dan luas 20 MP, serta penulisan ulang dengan GD. File upload tidak dilacak Git; hanya nama gambar yang diizinkan router, tanpa eksekusi script. Resolver gambar publik tetap dimiliki `app/view.php`, mendukung aset lama dan gambar upload. Edit tanpa file mempertahankan gambar lama; file sukses tidak dihapus otomatis pada replace/delete agar referensi lama tetap aman. Gambar baru dibersihkan pada kegagalan sebelum commit. `dashboard-auth/` tetap salinan sebelumnya dengan input path.
- Keputusan pengguna 14 November 2026: semua tampilan publik memakai HTML di root (`index.html`, `about.html`, `music.html`, `tour.html`, `showcase.html`, `news.html`, `news-detail.html`, `ticket-transaction.html`). Lima PHP publik lama hanya redirect 308 ke HTML dengan query dipertahankan. API PHP terpisah di `api/`; `site-config.js` mengatur direktori API publik tanpa kredensial. Baca [HTML_PHP_SEPARATION.md](HTML_PHP_SEPARATION.md). Keputusan ini menggantikan larangan versi HTML pada implementasi sebelumnya.
- `base.css` dan `script.js` dipakai bersama. CSS per halaman, `assets/`, serta [output/DESIGN.md](../output/DESIGN.md) yang tersedia di checkout menjadi referensi tampilan. Cocokkan dokumentasi desain dengan kode; sebagian catatan lama dapat tertinggal.
- `old/` memuat versi terpisah, `output/` memuat artefak hasil, dan `agent/` memuat dokumentasi kerja agent. Jangan menjadikan arsip atau artefak sebagai sumber implementasi aktif tanpa pemeriksaan.
- Telusuri alur input, proses, output, dan invariant sebelum mengedit. Perluas pemilik perilaku yang sudah ada dan hindari duplikasi.
- Pertahankan navigasi, tampilan responsif, akses keyboard, label, fokus, dan perilaku yang masih diperlukan saat mengintegrasikan PHP.
- Perubahan HTML menjadi PHP harus mencakup tautan, action form, path aset, dan include yang terdampak; jangan melakukan konversi massal tanpa kebutuhan.
- Jangan menambah framework backend atau mengganti MySQL tanpa instruksi pengguna. Hindari struktur layanan, dependensi, dan abstraksi yang belum diperlukan.
- Jangan menimpa perubahan rekan, menghapus arsip, atau merombak bagian di luar tugas. Koordinasikan perubahan file bersama dan kontrak frontend/backend.

## Peta UI dan sumber konfigurasi

Bagian berikut dipetakan dari kode pada 10 November 2026. Ini adalah baseline implementasi, bukan larangan mengubah UI/logika sesuai tugas pengguna. Baca file sumber sebelum mengedit; detail selector, spacing, media query, field, dan tipe SQL di file tersebut tetap menjadi sumber teknis utama. Perbarui aturan ini jika kontrak atau konfigurasi berubah. Jangan menyalin seluruh CSS/SQL ke dokumentasi sebagai sumber kebenaran kedua.

Semua halaman aktif saat pemeriksaan memakai `lang="en"`, UTF-8, viewport responsif, urutan stylesheet `base.css` lalu CSS halaman, dan `script.js` di akhir body. Pertahankan bahasa UI Inggris kecuali diminta menggantinya. Nama label Store mengarah ke `showcase.html`.

| Halaman/sumber | CSS khusus | Struktur/perilaku yang harus diperiksa saat integrasi |
|---|---|---|
| [index.html](../index.html) | [home.css](../home.css) | Hero, Tour Dates, Latest News, tautan Shared Playlist; ringkasan acara/berita membaca sumber yang sama dengan halaman detail. |
| [about.html](../about.html) | [about.css](../about.css) | Hero About, sejarah, anggota band, timeline arsip, kutipan. Timeline desktop bergantian kiri/kanan; mobile menjadi satu jalur. |
| [music.html](../music.html) | [music.css](../music.css) | Album terbaru/katalog, pencarian judul/album/isi lirik, Shared Playlist dengan rincian lirik, editorial Behind the Music, dan embed video terpisah. Tidak ada statistik plays yang berasal dari database. |
| [tour.html](../tour.html) | [tour.css](../tour.css) | Jadwal WIB, detail ID konser, status dari database; paket VIP hanya konsep non-bookable dan galeri hanya ilustrasi tanpa tanggal konser hardcoded. Tidak ada geolokasi atau reservasi aktif. |
| [showcase.html](../showcase.html) | [showcase.css](../showcase.css) | Hero Store, drop strip, filter katalog, kartu produk, lookbook, fan bag. |
| [news.html](../news.html) | [news.css](../news.css) | Featured news, update terbaru, pengumuman, fan story, form subscription. |
| [news-detail.html](../news-detail.html) | [news-detail.css](../news-detail.css) | Artikel dari slug, tanggal, artwork arsip, teks aman, More News, dan link informasi newsletter. Jangan mengarang penulis, jumlah views, kategori, atau gambar dari kolom yang belum ada. |
| [ticket-transaction.html](../ticket-transaction.html) | [ticket-transaction.css](../ticket-transaction.css) | Stepper, kartu acara, seat/quantity, delivery, kontak, payment, ringkasan dan timer statis. Belum ada proses transaksi server. |

Header memakai `.site-header`, `.header-inner`, `.main-nav`, `.header-icons`, dan link aktif `.active`. Footer memakai `.site-footer`, `.footer-inner`, `.footer-links`. Semua halaman publik memiliki markup HTML masing-masing; konten database mengisi template melalui `content.js`. Pertahankan ID anchor yang masih dipakai atau perbarui semua pemanggilnya; section ber-ID dan heading section mendapat scroll margin agar tidak tertutup sticky header.

`assets/` memuat gambar yang dipakai UI dan `assets/newpage/` memuat referensi visual PNG/SVG. `output/screenshots/` berisi screenshot referensi, bukan bukti kondisi UI terbaru. Jangan mengganti aset atau menyatakan hak lisensinya hanya dari nama file. Integrasi data tidak otomatis mengharuskan desain baru.

## Konfigurasi visual bersama

Sumber utama: [base.css](../base.css). Gunakan ulang token dan komponen yang sesuai; aturan halaman khusus tetap berlaku setelah CSS dasar.

| Token CSS | Nilai saat ini | Fungsi |
|---|---|---|
| `--paper` | `#f5f2ee` | Latar utama bernuansa kertas |
| `--paper-soft` | `#ebe7df` | Permukaan sekunder |
| `--ink` | `#080808` | Teks/permukaan gelap |
| `--line` | `#111` | Garis/border |
| `--muted` | `#6d6a64` | Teks sekunder |
| `--white` | `#fff` | Teks invers/permukaan putih |
| `--accent` | `#8f1111` | Aksen merah, penekanan interaksi |
| `--shadow` | `0 24px 70px rgba(0, 0, 0, 0.18)` | Bayangan komponen gambar tertentu |

- Identitas visual: editorial/concert poster, kontras hitam–kertas, garis tegas, tipografi tebal uppercase, tracking rapat untuk display, gambar monokrom. Jangan memperkenalkan tema/font/library UI baru tanpa kebutuhan tugas.
- Font aktual `Arial, Helvetica, sans-serif`, line-height body `1.5`; tidak ada import Google Fonts pada halaman aktif. Nilai font dan ukuran khusus harus dibaca dari CSS halaman, bukan diasumsikan dari README lama.
- `.container`: `min(100% - 48px, 1320px)`, menjadi `min(100% - 32px, 1320px)` pada lebar maksimum 980px. Body margin nol dan seluruh elemen memakai `box-sizing: border-box`.
- `.page` padding atas 70px; `.section` margin bawah 112px; `.section-tight` 78px. Pada maksimum 680px, `.page` menjadi 46px dan `.section` 74px; override halaman dapat berbeda.
- `.page-title`: `clamp(3.5rem, 9vw, 8rem)`, weight 900, line-height 0.9. `.section-title`: `clamp(2.2rem, 4.4vw, 4.1rem)`, weight 900, line-height 0.95. Halaman seperti About/Store memiliki display tersendiri.
- Header sticky `top: 0`, `z-index: 10`, latar kertas transparan dan blur 14px; tinggi minimum `.header-inner` desktop 78px. Link aktif memiliki garis aksen 3px.
- `.button`/`button`: tinggi minimum 46px, hitam/putih, uppercase; `.secondary` transparan; `.small` minimum 34px; `.full` selebar wadah. Pada maksimum 680px tombol umum melebar, dengan pengecualian `.small` dan toggle navigasi. `.button.disabled` adalah kelas tampilan; gunakan atribut `disabled` pada kontrol native jika memang tidak boleh dioperasikan.
- `.image-frame` memakai border dan clipping; gambar `object-fit: cover`, grayscale dan contrast. Hover gambar scale 1.035 dengan contrast lebih tinggi; jangan merusak rasio gambar atau menghilangkan alt text saat mengganti sumber data.
- Animasi aktual berbeda per komponen: navigasi 200ms ease-out untuk transform/opacity, tombol 180ms untuk background/color/transform, gambar 500ms untuk transform/filter. Jangan menganggap semua animasi sudah dibatasi pada transform/opacity.
- `[hidden]` ditetapkan `display: none !important`; filter bergantung padanya. Aturan reduced-motion saat ini hanya menonaktifkan transisi menu/toggle, belum seluruh motion atau smooth scroll. Target aksesibilitas tidak berarti kepatuhan penuh sudah diuji.

### Layout responsif

- Breakpoint umum aktif adalah `max-width: 980px` dan `max-width: 680px`; keduanya inklusif. Jika mengubah breakpoint navigasi, sinkronkan `MOBILE_NAV_QUERY` di JavaScript.
- Home: hero dan stream menjadi vertikal pada 980px; baris acara vertikal pada 680px. Music: featured release/embed menjadi satu kolom pada 980px; pencarian dan baris lirik menumpuk pada 680px.
- About: foundation menjadi satu kolom dan anggota menjadi dua kolom pada 980px; anggota serta timeline menjadi layout mobile pada 680px. Tour: intro/split menjadi vertikal pada 980px; baris jadwal menjadi blok pada 680px.
- Store: grid produk tiga kolom desktop, dua pada 980px, satu pada 680px. Featured product semula span dua kolom; pada 980px kembali span satu. Bag sticky desktop menjadi static pada 980px.
- News: featured story/fan story/newsletter menumpuk pada 980px; input dan tombol subscription menumpuk pada 680px. News Detail: artikel/sidebar dua kolom desktop; satu kolom pada 980px, sidebar menjadi dua kolom dengan newsletter full-width; komponen artikel/sidebar menjadi satu kolom pada 680px.
- Checkout memiliki breakpoint tambahan `max-width: 1060px` dan `max-width: 720px`. Pada 1060px, grid utama menjadi satu kolom, sidebar static, stepper empat kolom tanpa garis. Pada 720px, stepper dua kolom, seat/event/form field menjadi satu kolom. Jangan mengganti breakpoint khusus ini dengan breakpoint umum tanpa mengevaluasi layout.
- Saat UI berubah, periksa di sekitar breakpoint yang terdampak serta desktop/mobile. Targetkan label yang jelas, fokus terlihat, keyboard, pesan status/error yang dapat dibaca pembaca layar, dan touch target yang layak. Tombol kecil 34px serta cakupan reduced-motion adalah kondisi aktual yang perlu dievaluasi jika disentuh, bukan standar yang wajib dilestarikan.

## Kontrak JavaScript dan logika prototipe

Sumber: [script.js](../script.js), berisi enam initializer, termasuk `initializeSongDetails()` untuk pembukaan rincian lagu. Script dipakai semua halaman dan masing-masing initializer memeriksa keberadaan elemen. Pertahankan sifat aman pada halaman yang tidak memiliki komponennya. Initializer dipanggil sekali setelah markup tersedia. Music menunggu event `mcr:content-ready` dari `content.js` untuk filter/rincian lagu; jangan menduplikasi listener.

### Navigasi

- `initializeNavigation()` menemukan `.site-header .main-nav`, memberi ID `main-navigation` bila belum ada, membuat satu `.nav-toggle`, dan menambah `.nav-ready`.
- Toggle mengubah `.nav-open` serta `aria-expanded`; `aria-controls` mengarah ke ID navigasi. Klik link menutup menu; Escape menutup dan memindahkan fokus kembali ke toggle; perubahan matchMedia 980px menutup menu.
- Tanpa JS, `.nav-ready` tidak terpasang sehingga navigasi tidak sengaja tersembunyi oleh aturan menu siap-JS. Pertahankan progressive enhancement ini.

### Pencarian lirik

- `initializeLyricsFilter()` memakai `.search-form`, `#lyrics-search`, `.lyrics-table`, dan `.lyrics-row:not(.header)`. Status/empty state pada markup HTML memakai `[data-lyrics-status]` dan `[data-lyrics-empty]`; JS memakai ulang node tersebut agar tidak duplikat.
- Data pencarian baris berada di `data-search-text` berisi judul, album, dan lirik yang diisi melalui DOM dataset; fallback `textContent` hanya untuk markup lama. Input ditrim/dilowercase dan dicocokkan sebagai substring; `hidden`, jumlah hasil, dan empty state diperbarui saat input/submit.
- GET `api/music.php?q=...` menolak array, UTF-8 invalid, dan teks lebih dari 200 karakter; query awal dari `music.html?q=...` diteruskan ke API lalu JS memfilter baris. Konten database/pencarian memerlukan JavaScript. Ini filter atas maksimum 100 lagu yang dimuat, bukan pencarian tanpa batas atas semua record.
- Shared Playlist menampilkan rincian `<details class="song-details" id="song-<id>">`. Link Read Lyrics/hash membuka details yang benar melalui `initializeSongDetails()`; isi dirender sebagai teks dengan baris baru. Belum ada pemutaran audio per lagu atau data pribadi pemilik playlist.

### Kalkulasi tiket

- `initializeTicketSummary()` memerlukan `.quantity-control` dengan dua button dan output, `.summary-card`, radio `name="delivery"`, serta atribut `data-summary-item`, `data-summary-price`, `data-summary-service`, `data-summary-delivery`, `data-summary-delivery-price`, `data-summary-total`.
- Kuantitas awal dari output adalah 2 pada HTML saat ini; fallback JS 1. Tombol membatasi perubahan ke 1–6 dan dinonaktifkan pada batas. Ini perilaku prototipe, bukan validasi data server.
- Nilai demo: tiket `125 × quantity`, service fee `21.25 × quantity`, biaya tetap 10; delivery `will-call` 5 dan mobile 0. Total = subtotal tiket + service + biaya tetap + delivery; tampilan dolar dengan dua desimal. Contoh quantity 2/mobile menghasilkan `$302.50`, will-call `$307.50`.
- Radio memperbarui `.delivery-option.selected`, label/biaya delivery dan total; output quantity/total memakai pengumuman live. Timer `09:54` dan stepper adalah markup statis, bukan countdown/reservasi aktual.
- Jangan mengunci angka demo, batas 1–6, kebijakan refund, atau mata uang sebagai aturan bisnis final. Pada integrasi, server harus memvalidasi ulang dan menjadi sumber harga/biaya serta kuota.

### Embed musik

- `initializeYouTubePlayer()` memakai `[data-youtube-player]`, `data-src`, dan `[data-youtube-local-notice]`. Pada HTTP/HTTPS, iframe `src` diisi dari `data-src`; pada protokol lain iframe dilepas dan petunjuk server lokal ditampilkan.
- Video yang dikonfigurasi di markup adalah ID `G-I9csAflBs`, URL embed `https://www.youtube.com/embed/G-I9csAflBs?rel=0`, dengan link fallback ke video yang sama. Ini konfigurasi provider saat ini, bukan bukti izin lisensi yang diverifikasi.
- Iframe memiliki title, ukuran 560×315, lazy loading, `referrerpolicy="strict-origin-when-cross-origin"`, allowlist fitur, dan fullscreen; wadah responsif 16:9. Link tab baru memakai `rel="noopener noreferrer"`. Pertahankan fallback dan atribut keamanan/aksesibilitas yang relevan saat mengubah embed.

### Katalog dan fan bag

- `initializeMerchShowcase()` memerlukan `[data-store-filter]`, `[data-product-grid]`, `[data-store-empty]`, `[data-bag-items]`, dan `[data-bag-total]`. Kartu menggunakan `.product-card` dengan `data-category`, `data-name`, `data-price`; tombol memakai `[data-add-to-bag]`.
- Filter memakai nilai `all`, `apparel`, `music`, `accessory`; kategori dicocokkan persis dengan `data-category`, kartu diatur melalui `hidden`, dan pesan kosong muncul bila jumlah terlihat nol. Kategori dari database perlu dipetakan atau kontrak ini diperbarui secara menyeluruh.
- Bag memakai `Map` di memori dengan nama produk sebagai key; klik menambah quantity, harga dijumlahkan sebagai `price × quantity`, tampilan `$` tanpa pemaksaan dua desimal. Belum ada remove/decrement, persistensi, validasi stok, maupun pembedaan varian. Refresh menghilangkan pilihan.
- Implementasi `renderBag` saat ini membangun `innerHTML` dari nama produk yang semula statis. Sebelum menerima data eksternal/database, ganti konstruksi tak tepercaya dengan DOM/textContent atau encoding konteks yang tepat; jangan mempertahankan potensi XSS hanya demi kesamaan implementasi.
- Untuk data dinamis, gunakan ID produk/varian stabil sesuai kontrak, bukan nama sebagai identitas unik. Harga browser tetap hanya untuk tampilan.
- Link Continue Checkout saat ini hanya menuju `ticket-transaction.html`, tanpa transfer bag ke transaksi merch. Jangan mengklaim bahwa checkout tiket menerima isi bag atau order merch sudah tersimpan.

## Form dan fitur yang belum persisten

| Form | Konfigurasi aktual | Batas integrasi |
|---|---|---|
| Music `.search-form` | GET `music.php#lyrics`, input `q`, maxlength 200; JS menyaring katalog yang dimuat | Validasi/filter awal di server, dilanjutkan filter lokal tanpa request saat mengetik. |
| Store `.store-filter` | Select `#category-filter`, tanpa action/method eksplisit; listener change lokal | Bukan operasi penyimpanan produk atau filter server. |
| News `.newsletter-form` | GET `news.php#newsletter`, field `#email` dan tombol disabled | Placeholder; tidak menyimpan subscription atau mengirim email. |
| News Detail | Link `news.php#newsletter` | Tidak ada form/handler subscription server pada detail. |
| Checkout `#checkout-form` | GET ke `ticket-transaction.html`; submit button di luar form memakai `form="checkout-form"` | Bukan pembayaran/booking yang berfungsi; data sensitif berpotensi masuk query URL jika form dipakai. |

Field checkout saat ini: radio `delivery` (`mobile`/`will-call`), `name`, `email`, `phone`, `card`, `expiry`, `cvv`, `address`. Quantity berupa output, bukan field bernama yang otomatis dikirim. Jangan menganggap kuantitas, ID konser/jenis tiket, atau identitas user sudah tersedia pada request. Periksa setiap input dan petakan kontrak secara eksplisit sebelum membuat handler.

Form prototipe checkout belum memiliki validasi server atau kontrak bisnis lengkap; pencarian Music sudah memvalidasi query di server. Saat membuat fitur tulis, gunakan metode yang sesuai, error per field, encoding, validasi server, dan CSRF; jangan mempertahankan GET untuk penulisan/data sensitif. Auth, dashboard admin, playlist pribadi, subscription, email, pembayaran, dan reservasi belum tersedia hanya karena markup tampil.

## Kontrak skema MySQL

Sumber lengkap untuk tipe, nullability, default, indeks, dan constraint adalah [mcr_database_mysql.db](../mcr_database_mysql.db). Gunakan nama kolom yang ada; perbedaan seperti `cover_img` dan `cover_image` adalah nyata. Ringkasan ini membantu navigasi, bukan pengganti pemeriksaan skema/live database.

| Tabel | Kolom yang tersedia |
|---|---|
| `band_members` | `id`, `name`, `instrument`, `cover_img` |
| `news_articles` | `id`, `title`, `slug`, `content`, `news_date` |
| `users` | `id`, `name`, `email`, `password_hash`, `role`, `time_stamp`, `created_at` |
| `albums` | `id`, `title`, `release_date`, `cover_image` |
| `songs` | `id`, `album_id`, `title`, `lyrics` |
| `categories` | `id`, `name` |
| `products` | `id`, `category_id`, `name`, `price`, `details` |
| `product_variants` | `id`, `product_id`, `size`, `color`, `stock` |
| `merch_orders` | `id`, `user_id`, `total_amount`, `status` |
| `merch_order_items` | `id`, `order_id`, `variant_id`, `quantity`, `unit_price`, `subtotal`, `time_stamp`, `created_at`, `address` |
| `venues` | `id`, `name`, `city`, `country` |
| `concerts` | `id`, `venue_id`, `concert_date`, `status` |
| `ticket_types` | `id`, `concert_id`, `name`, `price`, `quota` |
| `ticket_orders` | `id`, `user_id`, `total_amount`, `status` |
| `ticket_order_items` | `id`, `order_id`, `ticket_type_id`, `quantity`, `unit_price`, `subtotal`, `order_code`, `time_stamp`, `created_at` |

- Database bernama `mcr_website`; semua tabel memakai InnoDB, charset `utf8mb4`, collation `utf8mb4_unicode_ci`, dan primary key `id` INT UNSIGNED AUTO_INCREMENT.
- Relasi: songs→albums; products→categories; product_variants→products; merch_orders→users; merch_order_items→merch_orders/product_variants; concerts→venues; ticket_types→concerts; ticket_orders→users; ticket_order_items→ticket_orders/ticket_types. Semua foreign key pada skrip memakai `ON UPDATE CASCADE ON DELETE RESTRICT`; jangan mengasumsikan cascade delete.
- `users.email` dan `news_articles.slug` memiliki unique key. Role default `user`; status order default `pending`; status konser default `scheduled`. Status/role adalah VARCHAR tanpa daftar lengkap nilai yang diizinkan pada skema; validasi nilai harus mengikuti keputusan tim.
- Harga/total/subtotal memakai DECIMAL(12,2) dan CHECK nonnegatif; stock/quota INT dengan CHECK nonnegatif; quantity item harus lebih besar dari nol. Batasan itu tidak otomatis menjamin total cocok dengan jumlah item atau melindungi kuota dari balapan request; logika transaksi tetap diperlukan.
- `user_id` order wajib. `order_code` tiket berupa INT nullable dan belum memiliki unique key. `address` berada pada item merch, bukan pada header order; jangan mengarang posisi atau jaminan uniknya.
- Skema belum memiliki tabel eksplisit playlist/subscription/kontak/message feed, kolom gambar produk/berita eksplisit, atau field lengkap payment/delivery seperti pada prototipe. Jangan membuat mapping fiktif; ajukan perubahan minimal sesuai kebutuhan yang terkonfirmasi.
- File berisi CREATE DATABASE/USE/CREATE TABLE, bukan bukti data dummy atau impor berhasil. `IF NOT EXISTS` tidak memperbarui tabel lama yang berbeda. Periksa skema aktual dan gunakan perubahan terkontrol, jangan menganggap menjalankan ulang skrip menyelesaikan perbedaan.

## Backend PHP native dan MySQL

- Periksa versi PHP, MySQL, ekstensi yang tersedia, dan lingkungan server sebelum memakai fitur yang bergantung pada versi atau menulis instruksi setup.
- Baca `mcr_database_mysql.db` sebelum mengubah akses data atau tabel. Konfirmasi apakah file ini versi final tim sebelum perubahan struktural; jangan meminta ulang skema yang sudah tersedia atau membuat skema tandingan. Keberadaan skrip bukan bukti bahwa skrip sudah diimpor atau diuji.
- Setelah kontrak data tersedia, letakkan koneksi database di satu tempat dan gunakan kembali. Jika belum ada pola akses, gunakan PDO MySQL apabila ekstensi tersedia; jangan mencampur PDO dan MySQLi tanpa alasan yang jelas.
- Pisahkan akses database dan logika bisnis dari markup sejauh diperlukan agar alur mudah diperiksa. Gunakan include/helper kecil sesuai kebutuhan; jangan membuat framework sendiri.
- Gunakan prepared statements untuk nilai input SQL. Identifier dinamis seperti nama kolom atau arah pengurutan harus dipilih dari allowlist, bukan disisipkan langsung dari input.
- Validasi input di server: tipe, panjang, format, rentang, nilai yang diizinkan, dan keberadaan referensi. Validasi browser hanya membantu pengalaman pengguna.
- Lakukan encoding output sesuai konteks. Untuk teks/atribut HTML gunakan `htmlspecialchars` dengan `ENT_QUOTES` dan encoding UTF-8; jangan menganggap satu fungsi sanitasi mengamankan HTML, SQL, URL, dan JavaScript sekaligus.
- Gunakan POST untuk perubahan data dan perlindungan CSRF pada operasi berbasis sesi/cookie. Jangan memakai GET untuk perubahan data atau mengirim data sensitif melalui URL.
- Jika modul autentikasi dikerjakan, gunakan `password_hash`/`password_verify`, regenerasi ID sesi setelah login, dan invalidasi sesi saat logout. Terapkan atribut cookie sesuai lingkungan, termasuk HttpOnly, SameSite, dan Secure saat HTTPS.
- Periksa autentikasi, role, dan kepemilikan objek di server pada setiap operasi yang memerlukannya. Menyembunyikan tombol admin bukan otorisasi; role admin tidak boleh dipilih bebas saat registrasi publik.
- Harga, biaya, total, stok, dan hak akses yang dikirim browser tidak boleh dipercaya. Jika pemesanan diaktifkan, hitung dari data server dan lindungi pembaruan stok/order dengan transaksi serta kendali konkurensi yang sesuai.
- Jangan menyimpan nominal uang dengan floating point jika membutuhkan perhitungan presisi; sesuaikan representasi dengan skema dan mata uang yang disepakati.
- Jangan mengumpulkan atau menyimpan nomor kartu/CVV pada proyek ini. Konfirmasi apakah checkout hanya simulasi; integrasi pembayaran nyata memerlukan kebutuhan dan penyedia yang disepakati terlebih dahulu.
- Form kartu pada prototipe `ticket-transaction.html` saat inisialisasi menggunakan GET. Jangan gunakan data pembayaran nyata atau menganggap form itu siap dipakai untuk transaksi.
- Jangan menaruh password database, token, dump berisi data pribadi, atau konfigurasi rahasia dalam Git/dokumentasi. Sebelum menambah konfigurasi lokal, periksa bahwa file rahasia diabaikan Git dan tidak dapat diunduh melalui web server.
- Tampilkan pesan kesalahan yang aman bagi pengguna; simpan detail teknis tanpa rahasia dalam log server. Jangan menampilkan query, kredensial, stack trace, atau error database mentah kepada pengguna.
- Jika upload diimplementasikan, validasi ukuran dan tipe file di server, gunakan nama aman, dan cegah eksekusi file unggahan.
- Perubahan skema harus terdokumentasi dan dapat direproduksi, termasuk dampak data serta cara pemulihan. Operasi destruktif atas data yang sudah ada memerlukan persetujuan eksplisit dan cadangan yang sesuai.

## Koordinasi dan kriteria selesai

- Gunakan jadwal sebagai rujukan prioritas; jangan mengubah sheet, pemilik tugas, atau status tim tanpa permintaan. Pembagian tugas individu backend harus dikonfirmasi sebelum mengambil alih modul rekan.
- Jangan menunda otorisasi sampai tahap akhir jika endpoint admin sudah dibuat lebih awal; endpoint tersebut harus terlindungi sebelum dipakai.
- Kontrak integrasi harus mencatat input, output, validasi, kebutuhan sesi/role, dan respons gagal yang benar-benar digunakan. Halaman publik saat ini memakai HTML dan endpoint JSON sesuai keputusan pengguna; dashboard tetap dirender PHP.
- Bedakan prototipe browser dari fitur persisten. Kalkulasi tiket dan fan bag di JavaScript tidak membuktikan adanya penyimpanan order atau pembayaran.
- Jalankan pemeriksaan terkecil yang membuktikan perubahan. Untuk dokumentasi, periksa konsistensi fakta, tautan lokal, dan diff; tidak perlu membuat tes aplikasi.
- Untuk perubahan kode, verifikasi alur normal dan kegagalan yang relevan. Perubahan database mencakup integritas relasi; autentikasi mencakup akses tanpa izin; transaksi mencakup input yang dimanipulasi dan konsistensi stok/order.
- Fondasi memiliki `tests/database.php` dan `tests/foundation.ps1`, dengan perintah terverifikasi di panduan setup. Uji integrasi memakai instance MySQL terisolasi, bukan mutasi database tim. Belum ada manifest npm, framework tes PHP, atau CI; jangan mengarang `npm test`/migration runner.
- Instruksi server Python di README hanya untuk pratinjau statis; server itu tidak mengeksekusi PHP. Hosting statis pada URL lama juga tidak membuktikan backend PHP/MySQL telah tersedia.
- Runtime lokal Laragon, PHP CLI 8.3.33 dengan PDO MySQL, dan database MySQL 8.4.3 telah diverifikasi. Koneksi memerlukan `DB_USER` dan `DB_PASSWORD` eksplisit dari environment; jangan hardcode kredensial atau menjadikan administrator default. Timezone konser disepakati WIB; kredensial email dan hosting akhir belum ditentukan. Periksa ulang lingkungan saat pekerjaan baru.
- `.gitignore` saat ini mengabaikan `.env`, `.env.local`, `.env.*.local`, log, direktori tooling/cache, dan beberapa file editor. Ini tidak otomatis mengabaikan semua file rahasia atau memuat environment ke PHP; periksa pola dan mekanisme pemuatan yang benar-benar digunakan.
- Untuk perubahan UI/JS, uji menu buka/tutup/Escape, filter beserta hasil kosong, embed HTTP dan fallback jika terdampak, quantity batas dan delivery, serta filter/add-to-bag. Jika data menjadi dinamis, uji karakter khusus dan ketidakcocokan data terhadap kontrak DOM. Jalankan hanya pemeriksaan yang relevan dengan perubahan dan laporkan batas pengujiannya.
- Saat menyerahkan pekerjaan, jelaskan perubahan, bukti pemeriksaan yang benar-benar dijalankan, serta kendala atau input yang masih dibutuhkan. Jangan commit, push, atau deploy hanya untuk menyatakan pekerjaan selesai.
- Perbarui fakta implementasi dan cara menjalankan aplikasi ketika PHP/MySQL benar-benar ditambahkan agar aturan ini tidak menjadi catatan yang menyesatkan.

## Batas integrasi HTML/API

API konten publik hanya GET/OPTIONS, CORS tanpa kredensial; jangan memperluas kebijakan ini ke data privat/operasi admin. Render teks API melalui `textContent`, bukan `innerHTML`. URL gambar berasal dari resolver backend. GitHub Pages hanya menyajikan frontend; backend HTTPS memerlukan hosting terpisah dan konfigurasi privat. Dashboard akses langsung tidak boleh ikut terbuka ke Internet tanpa pembatasan akses. Jangan mengklaim deployment berdasarkan simulasi lokal.

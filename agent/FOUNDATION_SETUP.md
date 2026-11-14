# Setup fondasi PHP–MySQL (WBS 3.1)

## Dashboard admin — Tugas #5

Instruksi pengguna memisahkan dua versi dashboard yang tetap memakai `database()` dari `app/database.php` dan template AdminLTE yang diberikan. Pembaruan tugas 13 November 2026 memperluas versi aktif menjadi CRUD seluruh 15 tabel; salinan versi auth tetap 10 tabel master sebelumnya. Tidak ada perubahan skema atau duplikasi database.

| Folder/URL | Perilaku |
|---|---|
| `dashboard/` → `/dashboard/` | Langsung menampilkan Administrator, tanpa akun, login, logout, atau provisioning admin. |
| `dashboard-auth/` → `/dashboard-auth/` | Salinan versi sebelumnya; login/logout dan pemeriksaan `users.role=admin` tetap aktif. |

Salinan dibuat sebelum mengubah versi akses langsung. Seluruh 13 file sumber disalin dan dibandingkan hash-nya; sesi aktif tidak disalin. Setelah itu hanya alamat README dan URL pengujian di salinan yang disesuaikan ke nama folder baru. Halaman utama tidak diubah. Router development mengizinkan index untuk versi langsung, index/login/logout untuk versi auth, dan tiga aset masing-masing versi. `internal/` tetap tidak disajikan.

### Membuka dashboard

Gunakan konfigurasi environment `DB_*` dan perintah server pada bagian konfigurasi di bawah, lalu buka `http://127.0.0.1:8000/dashboard/`. Server lokal yang telah berjalan pada port tersebut sudah memakai environment yang dikonfirmasi pengguna. Versi langsung memerlukan akses SELECT, INSERT, UPDATE, DELETE pada seluruh 15 tabel, tanpa perlu akun login dalam `users`. Sesi `mcr_dashboard` hanya untuk CSRF, flash message, dan snapshot form; tidak menyimpan identitas login. Membuka dashboard tidak membuat akun otomatis; pengelolaan akun tersedia melalui menu Users.

Panduan pengguna tersedia di [README akses langsung](../dashboard/README.md) dan [README versi dengan login](../dashboard-auth/README.md). Tidak ada kredensial database atau password akun di README. Perbedaan URL penting: `http://localhost/Kelompok/` pada Laragon saat diperiksa menunjuk salinan berbeda di `D:\ProggramApp\laragon\www\Kelompok`, sedangkan server port 8000 menunjuk workspace repositori ini. Salinan Laragon tersebut tidak diubah oleh pekerjaan pemisahan folder ini.

### Menggunakan versi dengan login

Akun admin lokal pengujian yang sebelumnya dibuat tetap berlaku; tidak ada reset password atau pembuatan akun baru pada pekerjaan ini. Untuk admin baru, alat CLI berada di `dashboard-auth/internal/create-admin.php`. Jalankan dari root proyek setelah environment database diisi; password 12–72 byte dikirim melalui stdin. Email duplikat ditolak dan pengguna lama tidak dipromosikan atau ditimpa.

```powershell
$adminName = Read-Host 'Nama admin'
$adminEmail = Read-Host 'Email admin'
$adminSecret = Read-Host 'Password admin (12–72 byte)' -AsSecureString
try {
    $adminPasswordInput = [Net.NetworkCredential]::new('', $adminSecret).Password
    $adminPasswordInput | & $php dashboard-auth/internal/create-admin.php $adminName $adminEmail
    if ($LASTEXITCODE -ne 0) { throw 'Pembuatan admin gagal; periksa pesan CLI.' }
} finally {
    Remove-Variable adminSecret,adminPasswordInput -ErrorAction SilentlyContinue
}
```

Versi auth memakai sesi `mcr_admin`, memeriksa ulang role/password hash setiap request, membatasi waktu tidak aktif 30 menit, dan meregenerasi ID sesudah login. Lima kegagalan login dalam satu sesi memberi jeda satu menit. Folder sesi tiap versi terpisah di `internal/sessions/`, diabaikan Git, dan harus dapat ditulis proses PHP. `.htaccess` masing-masing folder memblokir akses internal pada Apache; konfigurasi Apache untuk versi baru belum diuji. Siapa pun yang membuka versi akses langsung mendapat fungsi admin sesuai instruksi pengguna; tidak ada pembatasan akun pada versi itu.

### Kontrak pengelolaan data

Kedua versi menyediakan CRUD `band_members`, `albums`, `songs`, `news_articles`, `venues`, `concerts`, `ticket_types`, `categories`, `products`, `product_variants`. Versi aktif juga menyediakan `users`, `merch_orders`, `merch_order_items`, `ticket_orders`, dan `ticket_order_items`, sehingga mencakup seluruh 15 tabel skema. Pencarian literal, filter per relasi (termasuk beberapa relasi sekaligus), sort, pagination 5/10/15 baris, rincian teks, serta dialog konfirmasi tetap tersedia. Identitas, waktu pencatatan, dan nilai turunan diisi otomatis; tidak semua kolom SQL merupakan isian yang dapat diedit.

- GET hanya membaca/membuka form. Create/edit/delete memakai POST dan CSRF, termasuk versi akses langsung; sukses mengalihkan dengan 303.
- Nilai memakai prepared statements, identifier/sort dari allowlist, dan input divalidasi terhadap tipe, panjang, tanggal, harga, stok/kuota, serta referensi induk.
- Versi aktif `dashboard/` memakai upload gambar anggota band/album; versi `dashboard-auth/` tetap memakai input path sebelumnya. Resolver publik menerima aset lama di `assets/` dan nama file upload yang diizinkan. Isi/lirik/detail ditampilkan sebagai teks yang di-escape. Slug berita unik menggunakan huruf kecil, angka, dan hyphen.
- TIMESTAMP konser diinput WIB, disimpan/dibaca dalam UTC, ditampilkan WIB. DATETIME berita disimpan sebagaimana diisi.
- Update/delete memakai transaksi, row lock, dan snapshot HMAC untuk menolak form usang dengan 409. FK RESTRICT dipertahankan; tidak ada cascade manual.
- ID tidak ditemukan menghasilkan 404, input form invalid 422, konflik unique/FK 409, CSRF invalid 403, metode tidak didukung 405, dan database gagal 503 generik.

### Cakupan WBS 2.2/2.3 dan pesanan administrasi

Header dashboard memetakan Create/Read ke WBS 2.2 dan Update/Delete ke WBS 2.3. Semua 15 menu menyediakan operasi tersebut dengan aturan relasi yang sama. README pengguna menjelaskan urutan pengerjaan dan memberikan contoh commit message untuk pengumpulan; tidak ada commit, push GitHub, atau pengisian spreadsheet yang dilakukan oleh penyesuaian ini.

Pengguna memilih **pencatatan administrasi** untuk pesanan. Harga satuan dan jumlah merupakan catatan yang diisi admin; subtotal dihitung server, total header merupakan jumlah seluruh subtotal. `dashboard/internal/orders.php` memiliki perhitungan integer cents dan menolak subtotal/total yang melampaui DECIMAL(12,2). Harga atau jumlah item dapat diubah, item dapat dipindah antarpesanan, dan item dapat dihapus; semua total terkait diperbarui dalam transaksi yang sama. Induk dikunci berurutan sebelum item; agregasi memakai locking read agar tidak memakai snapshot lama saat dua proses mengubah pesanan bersamaan. Header yang masih memiliki item dan pengguna yang masih dirujuk pesanan mengikuti FK RESTRICT. Stok/kuota tidak otomatis berubah; ini tidak mengimplementasikan checkout, booking, ataupun pembayaran publik WBS 3.3.

Users memvalidasi email unik serta peran user/admin. Password baru 12–72 byte di-hash; password kosong pada edit mempertahankan hash lama. Hash tidak ditampilkan, dicari, diurutkan, atau diisi ke input browser. Akun admin terakhir tidak boleh dihapus/diturunkan agar salinan dashboard dengan login tetap dapat digunakan. Seluruh operasi user memakai transaksi dan perlindungan konflik form.

Run `.runtime/foundation-531d5687f5764d9696ece4de8e5e8ea3/` lulus Create/Read seluruh 15 tabel terlebih dahulu, kemudian Update/Delete seluruh 15 tabel. Pemeriksaan juga mencakup password update yang berhasil dipakai login pada versi auth, email duplikat/invalid, role invalid, password tidak bocor pada respons, admin terakhir, total/subtotal otomatis meskipun input dimanipulasi, pemindahan item, overflow dan rollback, dua proses HTTP/PHP menambah item ke pesanan yang sama, filter dua relasi, dan stok/kuota tetap. Regresi upload, konten publik, auth salinan, dan lint 24 file PHP lulus. Semua mutasi memakai MySQL terisolasi; database tim tidak dimutasi. `dashboard-evidence.json` memuat cakupan WBS 2.2/2.3; log MySQL mengonfirmasi shutdown selesai.

Pemeriksaan browser pada server lokal port 8000: kelima menu tambahan merespons 200; form item merchandise menampilkan dua pilihan relasi, quantity minimal 1 dan subtotal berupa output otomatis. Pada viewport 390×844, lebar dokumen 390 dan modal 352 piksel, tidak ada overflow halaman. Escape menutup modal dan fokus kembali ke Add record; viewport dikembalikan setelah uji. Pemeriksaan ini hanya membaca daftar/form pada database tim.

### Upload gambar pada versi aktif

Form anggota band dan album menggunakan `multipart/form-data`, input file, dan preview gambar tersimpan. Helper `dashboard/internal/images.php` memeriksa upload HTTP asli, ukuran input maksimal 2 MiB, MIME dari fileinfo, header dimensi/format, maksimal sisi 6000 piksel dan luas 20 MP, kemudian mendekode dan menulis ulang piksel memakai GD. Format JPG/PNG/WebP memperoleh nama acak 32 karakter hex; nama file dan path dari browser tidak menjadi lokasi penyimpanan. PHP lokal telah diperiksa memiliki ekstensi `fileinfo` dan `gd`; kedua ekstensi diperlukan untuk upload.

Penyimpanan berada di `dashboard/uploads/`, diabaikan Git kecuali `.gitignore` dan `.htaccess`. Router hanya melayani nama acak JPG/PNG/WebP yang cocok dengan allowlist dan berada dalam folder tersebut. `.htaccess` upload memblokir listing/CGI, menolak nama lain, dan menonaktifkan handler pada file gambar; Apache belum diuji. `app/view.php` tetap memiliki validasi gambar lokal yang dipakai halaman Music, sehingga cover upload langsung dapat dibaca tanpa memperluas akses ke seluruh folder dashboard.

Tidak memilih file pada edit mempertahankan gambar sebelumnya. Input path mentah ditolak. Validasi dan snapshot dilakukan sebelum menyimpan gambar; kegagalan SQL sebelum COMMIT membuang file baru. Jika hasil COMMIT tidak diketahui akibat koneksi putus, file dipertahankan untuk menghindari kehilangan gambar yang mungkin sudah tersimpan. File sukses tidak otomatis dihapus pada replace/delete, agar referensi lama atau referensi bersama tidak rusak. Saat memindahkan atau mencadangkan data, sertakan folder uploads bersama database; gambar tidak ikut melalui Git.

Uji upload ada di `dashboard/internal/test-images.ps1`, dipanggil runner fondasi pada MySQL terisolasi. Run `.runtime/foundation-bb697ac1548c4013950bcb07de609d79/` lulus multipart JPG/PNG/WebP, preview, cover Music, edit tanpa file, replace, penulisan ulang gambar yang diberi nama `.php` dan tambahan payload, penolakan file palsu/lebih dari 2 MB/dimensi berlebih, konflik snapshot tanpa file baru, rollback SQL tanpa file yatim, rute privat, dan retensi file saat record dihapus. Test trigger hanya dibuat pada database uji dan dibuang setelahnya; semua record/file gambar milik tes dibersihkan. Bukti `image-upload-evidence.json` tersimpan di run tersebut. Regresi CRUD kedua versi, konten publik, dan lint 23 file PHP juga lulus; data band lokal tidak dimutasi untuk pengujian upload.

### Bukti pemisahan dan pengujian

Runner `tests/foundation.ps1` menjalankan `dashboard/internal/test.ps1` **sebelum akun apa pun dibuat** di database uji, kemudian membuat akun untuk menjalankan `dashboard-auth/internal/test.ps1`. Run `.runtime/foundation-38c3176e9c944258987fe9a0809d0f94/` lulus sintaks 22 file PHP, regresi konten publik, CRUD 10 modul pada kedua versi, CSRF/validasi/FK/konflik, serta login/logout/role versi auth. Bukti terpisah: `dashboard-evidence.json` dan `dashboard-auth-evidence.json`. Semua mutasi uji memakai MySQL terisolasi, tidak mengubah data band atau akun pada database tim.

Smoke test database lokal yang sudah ada: GET `/dashboard/` → 200 dengan Administrator dan tanpa kontrol login/logout; GET `/dashboard-auth/` → 303 ke `login.php`. Folder/file login, logout dan create-admin tidak ada di versi langsung; token form dan validasi tetap bekerja. Server uji terisolasi ditutup pada cleanup.

Template mahasiswa asli tetap menjadi cadangan lokal di `.runtime/dashboard-template-original/`, bukan implementasi aktif. CSS AdminLTE asli beserta atribusinya dipertahankan pada kedua folder.

Pembaruan 14 November 2026: perintah environment/router tetap berlaku; root, Music, News/detail, dan Tour sekarang HTML dengan API PHP terpisah. Baca [HTML_PHP_SEPARATION.md](HTML_PHP_SEPARATION.md) untuk kontrak aktif dan konfigurasi GitHub Pages. Halaman tersebut memakai ekstensi `mbstring` selain PDO MySQL (keduanya tersedia pada PHP yang diuji). Baca [TESTING_3_2.md](TESTING_3_2.md) untuk pemeriksaan UI terkini. Referensi ke UI statis di bagian kontrak historis 3.1 tidak menyatakan kondisi setelah 3.2.

## Lingkungan yang diverifikasi

Pada 10 November 2026, PHP CLI 8.3.33 beserta PDO MySQL tersedia di `D:\ProggramApp\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe`. MySQL Laragon 8.4.3 aktif di `127.0.0.1:3306`. Apache 2.4.68 tersedia, tetapi jalur HTTP yang diuji pada pekerjaan ini memakai server development PHP dan `router.php`, bukan konfigurasi virtual host Apache.

Database tim `mcr_website` sudah tersedia: 15 tabel InnoDB, 11 foreign key, charset utf8mb4 dan collation utf8mb4_unicode_ci. Struktur telah dibandingkan dengan impor skrip lokal pada instance uji. Tidak ada impor, insert, update, atau delete yang dilakukan pada database tim. Tabel `albums` kosong saat pemeriksaan; jangan mengisinya dengan data uji tanpa koordinasi tim.

## Konfigurasi dan menjalankan aplikasi

Koneksi bersama tersedia di [app/database.php](../app/database.php). Konfigurasi dibaca dari environment proses PHP:

| Variabel | Jika belum diatur | Keterangan |
|---|---|---|
| `DB_HOST` | `127.0.0.1` | Host database; karakter yang diizinkan divalidasi sebelum membentuk DSN. |
| `DB_PORT` | `3306` | Angka port 1–65535. |
| `DB_NAME` | `mcr_website` | Nama database; huruf/angka/underscore. |
| `DB_USER` | Tidak ada default | Wajib diisi eksplisit; jangan bergantung akun root sebagai default aplikasi. |
| `DB_PASSWORD` | Tidak ada default | Harus diatur; nilai kosong hanya bila akun lokal memang memakai password kosong. |

[.env.example](../.env.example) hanya referensi nama variabel. PHP tidak otomatis memuat file tersebut atau `.env`; tidak ada dotenv/dependensi baru. Jangan menyimpan kredensial nyata dalam contoh, Git, atau dokumentasi.

Jalankan dari root repositori menggunakan PowerShell 7 (masukkan kredensial hanya di terminal lokal):

```powershell
$php = 'D:\ProggramApp\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe'
$env:DB_HOST = '127.0.0.1'
$env:DB_PORT = '3306'
$env:DB_NAME = 'mcr_website'
$env:DB_USER = Read-Host 'Database user'
$env:DB_PASSWORD = [Net.NetworkCredential]::new('', (Read-Host 'Database password (kosong jika akun lokal memang tanpa password)' -AsSecureString)).Password
& $php -d display_errors=0 -S 127.0.0.1:8000 router.php
```

Buka `http://127.0.0.1:8000/` untuk frontend dan `http://127.0.0.1:8000/api/albums.php` untuk alur baca. Hentikan server dengan Ctrl+C; hapus environment kredensial dari sesi setelah selesai menggunakan `Remove-Item Env:DB_USER, Env:DB_PASSWORD -ErrorAction SilentlyContinue`. Jangan menjalankan server development pada antarmuka publik.

Server harus dijalankan dengan `router.php`: router mengizinkan halaman/CSS/JS aktif dan gambar di `assets/`, tetapi tidak melayani konfigurasi, kode internal, dokumentasi agent, SQL, test, atau artefak runtime. Jika menambahkan route/jenis aset baru, perbarui allowlist dengan sengaja dan uji. Membuka PHP lewat Python/static hosting tidak mengeksekusi backend.

Untuk Apache 2.4 terdapat [.htaccess](../.htaccess) yang melarang listing direktori, direktori internal, dotfile, dan berkas dokumentasi/skema/log. Aturan itu memerlukan mod_rewrite serta AllowOverride yang sesuai. Konfigurasi Apache dan pemeriksaan HTTP pada virtual host belum diuji; jangan menganggap perlindungan aktif sebelum memeriksa request berkas privat pada server tersebut. Nginx/hosting lain memerlukan konfigurasi proteksi setara.

## Kontrak integrasi yang tersedia

### Koneksi bersama

Modul PHP berikutnya dapat memakai `require_once` ke `app/database.php`, lalu `database()`. Fungsi mengembalikan PDO yang sama selama satu request, charset utf8mb4, fetch asosiatif, exception mode, dan prepared statements native. Koneksi tidak otomatis dibuka ketika file di-include. Kredensial hilang atau DSN invalid ditolak; pemanggil menangani error ke respons aman. Jangan membuat koneksi duplikat per modul.

### GET /api/albums.php

- Akses baca publik tanpa sesi; belum ada operasi tulis atau auth di endpoint ini.
- Membaca `id`, `title`, `release_date`, `cover_image`, diurutkan `release_date DESC, id DESC`, maksimal 100 baris. Belum ada pagination atau filter request. Jangan menyimpulkan respons ini berisi semua album jika jumlahnya melebihi 100.
- Sukses HTTP 200 dengan `Content-Type: application/json; charset=utf-8` dan `X-Content-Type-Options: nosniff`. ID berupa integer; field nullable tetap null. Tabel kosong menghasilkan `{"data":[]}`.
- Contoh bentuk data dummy: `{"data":[{"id":1,"title":"Dummy album","release_date":"2006-01-01","cover_image":null}]}`.
- GET membaca data; OPTIONS menghasilkan 204 untuk CORS. Metode lain: HTTP 405, `Allow: GET, OPTIONS`, kode error `method_not_allowed`. CORS publik memakai `*` tanpa kredensial.
- Konfigurasi/database/query gagal: HTTP 503, `Cache-Control: no-store`, payload `{"error":{"code":"service_unavailable","message":"Content is temporarily unavailable."}}`.
- Error server hanya mencatat kelas exception dan konteks umum; pesan PDO/query/password tidak dikirim atau dicetak ke log aplikasi. Log detail MySQL diperiksa secara lokal sesuai kebutuhan.
- Konsumen UI harus melakukan encoding saat menampilkan data; string JSON yang berisi markup bukan HTML terpercaya. Music sekarang memakai HTML/JavaScript dan `api/music.php`; query album bersama di `app/content.php`. Endpoint album tetap tersedia untuk kompatibilitas kontrak baca 3.1.

## Menjalankan verifikasi ulang

[tests/foundation.ps1](../tests/foundation.ps1) membutuhkan PowerShell 7.4+ (`Start-Process -Environment`), executable PHP dengan PDO MySQL, serta distribusi MySQL 8.4. Verifikasi aktual menggunakan PowerShell 7.6.9 dan PHP 8.3.33.

```powershell
& .\tests\foundation.ps1 `
  -Php 'D:\ProggramApp\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe' `
  -MySqlBin 'D:\ProggramApp\laragon\bin\mysql\mysql-8.4.3-winx64\bin'
```

Runner membuat datadir MySQL terpisah di `.runtime/foundation-<id>/`, memakai port loopback sementara dan password acak. Skrip SQL hanya diarahkan ke `mcr_foundation_test` di instance tersebut; akun pembaca uji hanya diberi SELECT. Runner menghentikan proses yang dibuatnya dalam `finally` dan menghapus file konfigurasi/query yang berisi kredensial. Datadir/log/schema reference dipertahankan untuk inspeksi dan diabaikan Git. Proses MySQL tim pada port 3306 tidak dihentikan atau diubah. Folder uji lama dapat dibersihkan setelah memastikan prosesnya sudah berhenti dan jalur yang akan dihapus benar-benar folder hasil uji.

Uji meliputi impor skema, reuse koneksi, validasi konfigurasi, prepared statements, utf8mb4/emoji, sintaks PHP, respons album kosong/terisi/null/urutan, metode HTTP, respons gagal, halaman/aset statis, dan penolakan jalur privat/traversal. Hasil pelaksanaan serta batas verifikasi dicatat di [REPORT_3_1.md](REPORT_3_1.md).

Untuk melihat perbedaan sebelum/sesudah secara langsung, ikuti [TESTING_3_1.md](TESTING_3_1.md). Runner mencetak respons demo dan menyimpan `http-evidence.json` pada folder run lokal.

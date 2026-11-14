# Pemisahan HTML dan PHP — 14 November 2026

## Hasil dan alur

Halaman publik memakai HTML sebagai tampilan, JavaScript mengambil JSON, dan PHP membaca MySQL. `index.html` menjadi pintu masuk statis. Tidak ada perubahan skema atau data database tim. Dashboard tetap memakai PHP.

| Tampilan | API baca | Isi `data` pada respons sukses |
|---|---|---|
| `index.html` | `api/home.php` | `news`, `concerts` |
| `music.html` | `api/music.php?q=...` | `albums`, `songs`, `query` |
| `news.html` | `api/news.php` | `articles` |
| `news-detail.html?slug=...` | `api/article.php?slug=...` | `article`, `related` |
| `tour.html?concert=...` | `api/tour.php?concert=...` | `concerts`, `selected` |

`api/albums.php` mempertahankan kontrak `data` berupa array album. Daftar konten maksimal 100; ringkasan Home maksimal tiga. Shared Playlist tetap daftar lagu bersama; waktu konser WIB. About, Store, dan checkout tetap prototipe yang belum menyediakan transaksi publik.

`app/content.php` memiliki query bersama; `app/database.php` memiliki koneksi PDO; `app/public-api.php` memiliki respons API dan proyeksi data. `app/view.php` mempertahankan helper yang juga dipakai dashboard. Template header/footer PHP publik yang tidak dipakai telah dihapus. Markup sekarang dimiliki masing-masing HTML.

`content.js` mengisi template menggunakan `textContent`, menampilkan status loading/kosong/gagal, serta memicu `mcr:content-ready`. `script.js` menginisialisasi pencarian dan rincian lagu sesudah data Music tersedia, sekali saja. Tanpa JavaScript atau API, kerangka halaman/navigasi tetap tersedia, tetapi konten database dan pencariannya tidak tersedia.

Lima URL PHP lama mengalihkan 308 ke HTML dengan query dipertahankan ketika dilayani server PHP/Apache yang sesuai. GitHub Pages sendiri tidak menjalankan redirect PHP; gunakan tautan HTML baru.

## Menjalankan lokal

Gunakan environment `DB_*` dan perintah PHP/router dalam [FOUNDATION_SETUP.md](FOUNDATION_SETUP.md). Buka `http://127.0.0.1:8000/`; `site-config.js` dengan `apiBase: ""` mengambil API dari host yang sama. Jangan membuka HTML melalui `file://` untuk menguji integrasi.

Untuk pratinjau HTML tanpa backend, jalankan dari root:

```powershell
python tests/static-preview.py --port 8001
```

Buka `http://127.0.0.1:8001/MCR-Website/`. Server uji ini hanya menyajikan sumber publik yang diizinkan dan tidak menjalankan PHP.

Untuk simulasi dua server, jalankan backend PHP di port 8000, lalu:

```powershell
python tests/static-preview.py --port 8001 --api-base http://127.0.0.1:8000/api/
```

## GitHub Pages dan hosting PHP

Frontend ditujukan ke [GitHub Pages proyek](https://ffddosmmnt.github.io/MCR-Website/). URL repository GitHub menampilkan sumber/README; URL itu bukan URL laman yang dihosting. Kondisi deployment remote belum diverifikasi atau diubah oleh pekerjaan ini.

GitHub Pages hanya menyajikan frontend. Tim masih perlu memilih hosting HTTPS yang menjalankan PHP/MySQL, mengatur environment backend secara privat, lalu mengisi `apiBase` pada [site-config.js](../site-config.js) dengan URL direktori API hosting tersebut, diakhiri `/`. Nilai ini URL publik, bukan password/token. Jangan memasukkan kredensial database ke JavaScript.

Jika `apiBase` kosong di domain `.github.io`, halaman menampilkan pemberitahuan pratinjau statis tanpa mencoba mengunduh PHP. HTTPS frontend memerlukan HTTPS backend. Cover album berasal dari host backend; aset publik dan upload yang direferensikan harus tersedia di sana.

API publik mendukung GET dan OPTIONS (204), CORS `Access-Control-Allow-Origin: *`, tanpa cookie/kredensial. Metode lain 405; input invalid 400; record tidak ditemukan 404; kegagalan database 503 generik. Respons gagal tetap memiliki header CORS. Jangan menerapkan kebijakan CORS ini ke operasi admin atau data privat.

**Batas deployment:** `dashboard/` adalah administrator tanpa login sesuai keputusan pengguna terdahulu. Hosting backend yang dapat diakses Internet harus membatasi akses dashboard tersebut atau memakai versi `dashboard-auth/` dengan konfigurasi akses yang sesuai. Jangan mempublikasikan administrator tanpa pembatasan akses. Proteksi `.htaccess` memerlukan konfigurasi Apache yang benar dan belum diuji pada virtual host.

## Bukti pemeriksaan

Runner `tests/foundation.ps1` lulus pada run lokal `.runtime/foundation-f64207301c3c40ef849fa397f60defdd/`: sintaks 28 file PHP, fondasi PDO, API/HTML, validasi, CORS, WIB, redirect, 23 resource frontend, CRUD 15 tabel, transaksi/konkurensi, CSRF, upload gambar, serta regresi dashboard auth. `node --check content.js` dan `node --check script.js` lulus. Bukti lokal mencakup `http-evidence.json`, `content-evidence.json`, dan bukti dashboard; artefak runtime tidak untuk dipublikasikan.

Browser memakai frontend statis pada prefix `/MCR-Website/` dan API di port berbeda, dengan data dummy pada MySQL terisolasi. Teramati: Home memuat berita/konser; pencarian frasa di isi lirik menghasilkan satu lagu dan frasa tidak cocok menghasilkan nol; Read Lyrics membuka rincian yang sesuai; artikel menampilkan markup berbahaya sebagai teks; konser tampil `17 Nov 2026, 20:00 WIB`. Tanpa backend, hero/navigasi tetap tampil dengan pesan kegagalan konten. Pada viewport 390×844, menu buka/Escape bekerja dan tidak ada overflow horizontal Home. Viewport dikembalikan setelah pengujian.

Bukti tampilan: [Home dengan API](../output/screenshots/html-api-home.jpg) dan [Home mobile tanpa backend](../output/screenshots/html-static-mobile.jpg). Ini simulasi lokal, bukan bukti deployment GitHub Pages. Tidak ada commit, push, deployment, atau perubahan `.gitignore` dalam pekerjaan ini.

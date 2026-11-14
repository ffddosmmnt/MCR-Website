# Testing section 3.2 — Melihat data pada halaman

Catatan 14 November 2026: tampilan sekarang HTML + API PHP. Baca [panduan pemisahan](HTML_PHP_SEPARATION.md) untuk simulasi GitHub Pages dan bukti terbaru.

## Menjalankan aplikasi lokal

Gunakan langkah environment dan server PHP di [FOUNDATION_SETUP.md](FOUNDATION_SETUP.md). Perintah server tetap memakai `router.php` dari root proyek. MySQL Laragon harus aktif untuk mengakses database tim.

Setelah server port 8000 berjalan, buka:

| Halaman | URL dan yang diperiksa |
|---|---|
| Home | `http://127.0.0.1:8000/` — ringkasan berita dan konser |
| Music | `http://127.0.0.1:8000/music.html` — album, pencarian, Shared Playlist dan rincian lirik |
| News | `http://127.0.0.1:8000/news.html` — daftar dan tautan Read More ke slug artikel |
| Tour | `http://127.0.0.1:8000/tour.html` — jadwal WIB dan Show Details untuk ID acara |

Database tim masih kosong pada tabel konten saat awal pengembangan 3.2. Pesan “No albums/news/tour dates...” adalah hasil yang benar, bukan alasan untuk menampilkan konten hardcoded seolah berasal dari database. Screenshot berisi data dummy ada di [REPORT_3_2.md](REPORT_3_2.md).

## Demo data berisi tanpa menyentuh database tim

Jalankan dari PowerShell 7.4+ pada root proyek:

```powershell
& .\tests\foundation.ps1 `
  -Php 'D:\ProggramApp\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe' `
  -MySqlBin 'D:\ProggramApp\laragon\bin\mysql\mysql-8.4.3-winx64\bin'
```

Runner tetap menguji fondasi, lalu otomatis menjalankan `tests/content.ps1` dengan instance database uji yang baru. Jangan menjalankan fixture INSERT/UPDATE-nya pada `mcr_website` milik tim. Runner tidak memerlukan penghentian MySQL Laragon utama.

Perubahan yang dibuktikan tes:

1. News dan Tour kosong → fixture berita/lagu/venue/konser ditambahkan pada database uji → API memuat isinya dan JavaScript mengisi HTML.
2. Frasa hanya di isi lirik → pencarian menghasilkan satu lagu; kata yang tidak ada → nol hasil.
3. Judul berita diperbarui pada database uji → judul pada Home/detail berubah tanpa mengedit HTML.
4. Konser `17 November 2026 13:00 UTC` → tampilan `17 Nov 2026, 20:00 WIB`.
5. Database gagal → API menghasilkan 503 aman; seluruh kerangka HTML tetap 200 dan menampilkan pesan konten tidak tersedia.

Cari baris `PASS: separated HTML/PHP...` dan tunggu prompt kembali agar cleanup proses uji selesai. Perhatikan lokasi `CONTENT EVIDENCE: .../content-evidence.json`; file memuat hasil respons dan pemeriksaan yang sudah lulus. Run berhenti dengan exception berarti ada kegagalan meskipun beberapa baris PASS sebelumnya sudah tercetak.

Server uji berhenti setelah runner selesai; port sementara pada run bukan URL demo yang tetap aktif. Artefak `.runtime/` tidak dipublikasikan. Untuk melihat UI pada browser sendiri, jalankan aplikasi dengan database lokal yang benar; konten final perlu dimasukkan oleh pemilik data tim atau gunakan lingkungan demo terpisah yang disepakati.

## Checklist manual UI

- Music: masukkan judul, nama album, atau frasa lirik; jumlah hasil berubah. Bersihkan input untuk menampilkan kembali seluruh daftar yang dimuat. Pencarian dibatasi pada maksimum 100 lagu yang dimuat, bukan seluruh database tanpa batas.
- Klik Read Lyrics; rincian lagu yang sesuai terbuka. Shared Playlist sama untuk semua pengunjung dan tidak mengklaim memutar audio per lagu.
- News: klik Read More atau judul artikel; URL memiliki `news-detail.html?slug=...`, judul dan isi sesuai record, baris baru tetap terbaca. Kolom yang null ditangani tanpa menampilkan error PHP.
- Tour: waktu berlabel WIB. Klik Show Details; ID konser pada URL dan detail acara cocok. Booking belum aktif, termasuk pada status yang bertuliskan scheduled.
- Home: berita/acara sesuai halaman daftar; label Tour Dates tidak menjanjikan bahwa semua tanggal masih mendatang.
- Coba viewport desktop dan mobile sekitar breakpoint 980/680px. Buka menu mobile, lalu tekan Escape; menu menutup dan fokus kembali pada tombol. Teks panjang tidak menyebabkan scroll horizontal.
- URL PHP lama seperti `/music.php` beralih ke `/music.html`. Untuk artikel, `/news-detail.html?slug=<slug-yang-ada>` tetap mempertahankan slug.

## Respons error yang diharapkan

Uji berikut bersifat read-only pada server lokal yang sudah berjalan:

```powershell
$baseUrl = 'http://127.0.0.1:8000'
Invoke-WebRequest "$baseUrl/api/article.php?slug=slug-yang-tidak-ada" -SkipHttpErrorCheck | Select-Object StatusCode
Invoke-WebRequest "$baseUrl/api/tour.php?concert=-1" -SkipHttpErrorCheck | Select-Object StatusCode
Invoke-WebRequest "$baseUrl/api/music.php?q[]=invalid" -SkipHttpErrorCheck | Select-Object StatusCode
Invoke-WebRequest "$baseUrl/app/content.php" -SkipHttpErrorCheck | Select-Object StatusCode
```

Ekspektasi berurutan: **404, 400, 400, 404** pada router development. Untuk input sah yang tidak ada record-nya, gunakan ID/slug yang dipastikan tidak ada pada lingkungan tersebut. Jangan mematikan MySQL tim atau mengubah passwordnya untuk menguji kegagalan; skenario itu sudah memakai instance/kredensial uji terpisah di runner.

## Yang belum dibuktikan oleh pengujian ini

Pembayaran, order, login, email terkirim, playback audio per lagu, aturan status tiket, dan efektivitas konfigurasi Apache tidak otomatis terbukti oleh keberhasilan halaman baca. Lihat batas pada [laporan 3.2](REPORT_3_2.md); lanjutkan ke WBS berikutnya sesuai dependensinya.

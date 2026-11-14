# Panduan testing — Melihat perbedaan hasil fondasi 3.1

**Catatan versi:** perbandingan Music statis pada dokumen ini merekam kondisi setelah 3.1. Setelah 3.2, Music menjadi `music.php`; contoh halaman yang tetap statis pada runner diganti menjadi `about.html`. Gunakan [TESTING_3_2.md](TESTING_3_2.md) untuk kondisi sekarang, dan jangan mengharapkan Music tetap 200 saat database gagal.

## Apa yang seharusnya berbeda?

WBS 3.1 menambahkan koneksi PHP–MySQL dan endpoint baca album. Tampilan Home/Music belum dihubungkan ke database; perubahan tampilan dinamis merupakan pekerjaan WBS 3.2. Karena itu, membuka `music.html` saja belum cukup untuk membuktikan backend bekerja.

| Yang diperiksa | Frontend statis saat ini | Endpoint backend 3.1 |
|---|---|---|
| Sumber album | Markup di `music.html` | Tabel `albums` melalui PDO |
| Saat tabel database kosong | Kartu album hardcoded tetap tampil | GET `/api/albums.php` menghasilkan HTTP 200, `{"data":[]}` |
| Setelah album dummy ditambahkan di database uji | Markup tidak otomatis berubah | Respons GET memuat dua album dummy |
| Kredensial database salah | Halaman statis masih dapat dimuat | API menghasilkan HTTP 503 dengan pesan aman |
| POST ke endpoint album | Tidak relevan dengan tampilan | HTTP 405, karena endpoint hanya menerima GET |

Perbandingan ini menunjukkan perbedaan sumber data dan perilaku, bukan klaim bahwa UI sudah dinamis. Data uji hanya dimasukkan ke instance MySQL terpisah; tidak perlu mengisi atau menghapus data tim untuk mendemonstrasikannya.

## 1. Jalankan demo otomatis sebelum/sesudah

Buka **PowerShell 7.4 atau lebih baru** di root proyek. Windows PowerShell 5.1 bawaan lama belum mendukung parameter yang dibutuhkan runner. Jika terminal sedang menjalankan server PHP secara foreground, gunakan terminal kedua.

```powershell
Set-Location 'D:\Kuliah\Semester 5\WebProgramming\Kelompok'

& .\tests\foundation.ps1 `
  -Php 'D:\ProggramApp\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe' `
  -MySqlBin 'D:\ProggramApp\laragon\bin\mysql\mysql-8.4.3-winx64\bin'
```

Runner menggunakan binary Laragon tetapi membuat instance database sendiri dengan port dan kredensial uji. MySQL utama Laragon tidak harus dimatikan dan database tim `mcr_website` tidak dimutasi. Tunggu perintah selesai dan prompt kembali agar proses uji juga selesai dibersihkan.

Cari keluaran berikut (contoh bentuk output, bukan hasil baru sampai kamu menjalankan perintah):

```text
DEMO BEFORE (empty test database): HTTP 200 {"data":[]}
DEMO AFTER (2 dummy albums inserted): HTTP 200 {"data":[...dua album dummy...]}
DEMO POST (read-only endpoint): HTTP 405 {"error":{...}}
DEMO STATIC UI (invalid database credentials): HTTP 200 /music.html still loads; it is not database-driven yet.
DEMO FAILURE (invalid database credentials): HTTP 503 {"error":{...}}
DEMO FAILURE (missing database user): HTTP 503 {"error":{...}}
DEMO EVIDENCE: ...\http-evidence.json
PASS: foundation checks complete. Isolated run artifacts: ...
```

Interpretasi:

1. **BEFORE → AFTER:** endpoint yang sama menghasilkan data berbeda setelah perubahan database uji, tanpa mengedit kode PHP/HTML.
2. **STATIC UI → FAILURE:** halaman statis tetap 200, tetapi API gagal 503 ketika kredensial uji salah. Halaman yang terlihat normal bukan bukti koneksi database berhasil.
3. **POST → 405:** endpoint tidak menerima metode yang tidak disediakan; ini hasil uji yang diharapkan, bukan bug.
4. Baris **PASS** merangkum assertion yang benar-benar lulus. Jika muncul exception, lihat pesan dan log lokal; jangan menyatakan pengujian lulus hanya karena beberapa baris PASS sudah tercetak.

Judul dummy mengandung emoji dan string `<script>` untuk membuktikan karakter tersimpan/dikembalikan sebagai data JSON. Jangan menyalinnya ke `innerHTML` untuk demo UI; respons ini tidak menjamin keamanan rendering frontend.

## 2. Baca bukti respons yang disimpan

Salin **lokasi run yang dicetak oleh perintah yang baru kamu jalankan**, lalu buka `http-evidence.json` di dalamnya dengan editor. File memuat status/body sebelum dan sesudah, respons metode salah, serta perbandingan UI statis dengan kegagalan konfigurasi. Bukti tidak memuat password atau data pengguna nyata.

Contoh membaca dengan PowerShell (ganti path contoh dengan lokasi aktual):

```powershell
$evidencePath = Read-Host 'Tempel path lengkap http-evidence.json dari output tes'
$evidence = Get-Content -LiteralPath $evidencePath -Raw | ConvertFrom-Json
$evidence.before | ConvertTo-Json -Depth 6
$evidence.after | ConvertTo-Json -Depth 6
```

File ini adalah bukti run lokal, bukan endpoint browser yang tetap berjalan. Runner menghentikan server uji setelah selesai. Folder `.runtime/` juga memuat datadir/log/private key uji; jangan mengunggah seluruh folder ke GitHub.

## 3. Cek endpoint database tim melalui browser

Untuk pemeriksaan read-only pada database tim:

1. Hidupkan MySQL Laragon.
2. Ikuti blok konfigurasi environment dan perintah menjalankan PHP pada [FOUNDATION_SETUP.md](FOUNDATION_SETUP.md). Gunakan `router.php`; jangan membuka berkas API dengan `file://`, server Python, atau menganggap virtual host Apache sudah dikonfigurasi sama.
3. Setelah server PHP aktif di port 8000, buka [halaman Music statis](http://127.0.0.1:8000/music.html) dan [endpoint album](http://127.0.0.1:8000/api/albums.php) pada dua tab.
4. Tekan F12, buka tab **Network**, lalu reload endpoint album. Pilih request `albums.php`; periksa **Status**, **Response**, dan **Content-Type**. Respons sukses adalah JSON dengan HTTP 200.

Jika tabel tim masih kosong, hasil `{"data":[]}` adalah respons sukses yang benar. Demo dua album dummy berada pada langkah 1; jangan mengharapkan data uji muncul di database tim. Tampilan Music masih berisi data hardcoded sampai WBS 3.2 dikerjakan.

Untuk cek status tanpa browser, gunakan terminal kedua setelah server port 8000 aktif:

```powershell
$response = Invoke-WebRequest 'http://127.0.0.1:8000/api/albums.php'
$response.StatusCode
$response.Content

$wrongMethod = Invoke-WebRequest 'http://127.0.0.1:8000/api/albums.php' -Method POST -SkipHttpErrorCheck
$wrongMethod.StatusCode
$wrongMethod.Content

$privateFile = Invoke-WebRequest 'http://127.0.0.1:8000/app/database.php' -SkipHttpErrorCheck
$privateFile.StatusCode
```

Ekspektasi: GET **200**, POST **405**, berkas internal **404** pada router development. Request itu tidak menulis data. Kasus password salah/konfigurasi hilang sudah diuji otomatis di instance terpisah; tidak perlu mengubah password database tim atau mematikan server yang sedang digunakan rekan.

## 4. Bukti untuk laporan/presentasi

Ambil screenshot keluaran BEFORE/AFTER, tab Network respons GET, dan respons POST 405. Jelaskan bahwa ini membuktikan fondasi baca PHP–MySQL; belum membuktikan seluruh website dinamis, checkout, login, atau email sudah berfungsi. Jangan menampilkan terminal yang sedang berisi kredensial.

Catat: tanggal pengujian, perintah, skenario, status/body yang diamati, lulus/gagal, dan lokasi bukti lokal. Hasil fondasi sebelumnya ada di [REPORT_3_1.md](REPORT_3_1.md); hasil tes baru perlu ditafsirkan dari run aktual.

## Hasil demo yang sudah dijalankan

Demo pada 10 November 2026 telah dijalankan ulang dengan perubahan keluaran tes ini dan lulus:

| Skenario aktual | Status | Hasil |
|---|---|---|
| Sebelum INSERT pada database uji | 200 | `data` berisi array kosong |
| Setelah INSERT dua album dummy | 200 | Dua album dikembalikan, terbaru lebih dahulu; emoji dan null tetap terjaga |
| POST ke endpoint baca | 405 | `method_not_allowed` |
| Music statis dengan kredensial uji invalid | 200 | Halaman tetap dapat dimuat |
| API dengan kredensial uji invalid | 503 | `service_unavailable`, tanpa detail kredensial |
| API tanpa user database | 503 | `service_unavailable` |

Bukti lokal run tersebut ada di `.runtime/foundation-64ee2f22b6034781a72cbdcc5a11c950/http-evidence.json`. Lokasi ini adalah snapshot run; perintah berikutnya akan mencetak lokasi baru. Uji ini menggunakan instance MySQL terpisah dan tidak mengubah database tim.

## Jika hasilnya berbeda

| Gejala | Yang perlu diperiksa |
|---|---|
| `-Environment` atau `-SkipHttpErrorCheck` tidak dikenal | Jalankan PowerShell 7.4+, bukan Windows PowerShell 5.1. |
| PHP/MySQL executable tidak ditemukan | Periksa versi dan lokasi binary Laragon; sesuaikan argumen path, bukan source aplikasi. |
| Koneksi HTTP ditolak | Server PHP belum berjalan, proses sudah berhenti, atau port berbeda. Instance uji otomatis memang berhenti setelah tes. |
| GET album 503 | Periksa environment proses PHP, MySQL aktif, database/tabel tersedia; log server mencatat konteks aman. |
| Halaman Music sama seperti sebelumnya | Diharapkan pada fondasi 3.1; integrasi UI ada di section 3.2. |
| File privat terbaca | Pastikan memakai `router.php`; jika memakai Apache, verifikasi `.htaccess`/AllowOverride. Jangan menganggap hasil proteksi server development berlaku otomatis pada server lain. |

# Laporan hasil WBS 3.1 — Fondasi

Tanggal: 10 November 2026, Asia/Jakarta. Pelaksana teknis: agent pada chat ini, atas instruksi Abyan. Laporan ini tidak mengubah pembagian kerja tim atau status spreadsheet.

## Hasil dan status

**T31-A, T31-B, dan checkpoint fondasi selesai untuk lingkungan lokal yang diuji.** PHP dapat membaca database MySQL melalui koneksi bersama dan endpoint publik yang aman untuk satu operasi baca. Dokumentasi setup ada di [FOUNDATION_SETUP.md](FOUNDATION_SETUP.md).

| Pemeriksaan | Hasil aktual |
|---|---|
| Laragon | Instalasi 8.7.0 terdaftar di `D:\ProggramApp\laragon`. |
| PHP yang digunakan | CLI 8.3.33; ekstensi PDO dan pdo_mysql tersedia. Versi ini dipilih secara eksplisit untuk pengujian, bukan klaim versi aktif Apache Laragon. |
| MySQL tim | 8.4.3, aktif di `127.0.0.1:3306` setelah pengguna menghidupkan Laragon. |
| Database tim | `mcr_website` tersedia, 15 tabel InnoDB, 11 foreign key, utf8mb4/utf8mb4_unicode_ci. |
| Kecocokan skema | SHOW CREATE TABLE semua 15 tabel cocok dengan hasil impor skrip lokal pada MySQL 8.4.3; hanya penghitung AUTO_INCREMENT yang dinormalisasi saat dibandingkan. Tidak ditemukan selisih definisi dalam perbandingan tersebut. |
| Alur baca pada database tim | PHP HTTP GET `/api/albums.php` menghasilkan 200 dan `{"data":[]}`; jumlah album saat dicek 0. Tidak ada data dummy ditambahkan ke database tim. |
| Apache tersedia | Binary melaporkan versi 2.4.68. Virtual host/modul PHP dan efektivitas `.htaccess` belum diuji melalui Apache. |

Kredensial akses lokal diberikan pengguna dan digunakan hanya untuk pemeriksaan read-only. Password tidak dicatat di laporan atau kode. Skema lokal belum dinyatakan sebagai desain final tim oleh pengguna; kecocokan teknis telah diverifikasi dan tidak ada perubahan struktural pada database tim.

## Implementasi

- [app/database.php](../app/database.php): satu koneksi PDO per request, konfigurasi environment, validasi DSN, utf8mb4, prepared statements native, error mode exception.
- [api/albums.php](../api/albums.php): GET album maksimal 100, payload konsisten, 405 untuk metode lain, dan 503 aman ketika layanan gagal. Tidak menambahkan endpoint diagnostik publik atau operasi tulis.
- [router.php](../router.php): route development terkontrol, aset/halaman aktif tetap tersedia, jalur privat dan traversal ditolak. UI yang ada tidak dikonversi secara massal.
- [.htaccess](../.htaccess): aturan Apache untuk melindungi file/direktori internal; efektivitas pada virtual host perlu diverifikasi saat jalur Apache dipakai.
- [.env.example](../.env.example) dan [.gitignore](../.gitignore): referensi variabel nonrahasia serta pengecualian runtime lokal.
- [tests/database.php](../tests/database.php) dan [tests/foundation.ps1](../tests/foundation.ps1): pemeriksaan CLI dan integrasi HTTP/MySQL terisolasi yang dapat diulang.

## Bukti verifikasi

Runner terakhir dijalankan dengan perintah yang tercantum di setup. Semua kelompok pemeriksaan berikut lulus:

1. Skrip schema diimpor ke instance baru `mcr_foundation_test`: 15 tabel dan 11 foreign key.
2. Konfigurasi kredensial hilang serta port DSN invalid ditolak; koneksi dipakai ulang; native prepares aktif; string emoji dan markup dummy dapat round-trip melalui prepared statement dengan utf8mb4.
3. `php -l` lulus pada `app/database.php`, `api/albums.php`, `router.php`, dan `tests/database.php`.
4. HTTP 200 saat album kosong/berisi; urutan, null, charset, dan header JSON sesuai kontrak; POST menghasilkan 405.
5. Halaman root, Music, CSS, JavaScript, dan gambar uji tetap bisa dibaca; `.env`, `.env.example`, Git config, kode app, aturan agent, test, skrip SQL, router, unknown path, dan jalur traversal yang diuji menghasilkan 404.
6. Password uji invalid dan konfigurasi user kosong menghasilkan 503 JSON generik tanpa SQLSTATE, stack trace, user database, atau password pada body.
7. Pemeriksaan terpisah ke MySQL tim dengan mysql CLI berhasil; SHOW CREATE TABLE seluruh tabel dibandingkan dengan instance uji; request PHP HTTP ke database tim berhasil dengan hasil kosong yang benar.

Artefak run terakhir di `.runtime/foundation-6afbcb4c39f8444aae8b1b7c5c2bd745/`; metadata pembanding live di `.runtime/live-schema.txt`. Artefak tersebut lokal, bukan syarat menjalankan aplikasi dan tidak dilacak Git. Seluruh proses uji yang dibuat agent telah dihentikan. MySQL Laragon milik pengguna tetap berjalan. Runner memakai shutdown MySQL eksplisit, menunggu monitor Windows keluar, dan menyediakan fallback penghentian pohon proses agar proses anak tidak tertinggal. Log run terakhir mengonfirmasi Shutdown complete dan file kredensial sementara sudah dihapus.

Dua pemeriksaan awal runner diperbaiki sebelum run terakhir: jumlah foreign key yang benar adalah 11, dan PDO mengembalikan nilai atribut emulasi dalam bentuk yang perlu dinormalisasi ke boolean. Kegagalan awal itu adalah kekeliruan assertion, bukan klaim bahwa skema/driver harus diubah.

## Batas dan langkah berikutnya

- Hasil sukses dengan data dummy dibuktikan di instance terpisah. Database tim kosong pada tabel album dan belum diberi seed oleh pekerjaan ini.
- Belum ada integrasi UI Music, login/role, order, email, atau fitur WBS 3.2–3.5; jangan menandainya selesai berdasarkan fondasi ini.
- Autentikasi aplikasi belum diperlukan oleh endpoint baca album publik; operasi yang nantinya privat/tulis harus menambah kontrol sesuai kontraknya.
- Runtime aplikasi tidak menyimpan kredensial lokal. Operator harus mengatur environment ketika menjalankan server; untuk penggunaan di luar development lokal, gunakan akun aplikasi dengan hak minimum dan konfigurasi server yang diuji.
- `.htaccess` disediakan tetapi pengujian HTTP yang lulus menggunakan router PHP. Sebelum memakai Apache/hosting lain, verifikasi kembali penolakan berkas privat serta konfigurasi PHP/servernya.
- Langkah berikutnya: T32-A dapat memakai `database()` atau endpoint album ini dan mengintegrasikan UI Music setelah pembagian modul disepakati. Tabel/field yang berubah oleh pekerjaan section 2 harus dikomunikasikan dan diperiksa ulang.

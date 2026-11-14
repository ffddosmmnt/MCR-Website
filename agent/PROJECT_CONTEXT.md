# Konteks proyek dan input tim

> Pembaruan 14 November 2026: tampilan publik kini HTML + JavaScript dan API PHP terpisah. Rujuk [HTML_PHP_SEPARATION.md](HTML_PHP_SEPARATION.md); catatan render PHP terdahulu adalah riwayat, bukan arsitektur aktif. Hosting backend HTTPS belum ditentukan.

Pembaruan 13 November 2026: pengguna meminta penyesuaian dashboard dengan tugas CRUD **semua tabel** dalam dua tahap WBS 2.2 (Insert/Select) dan 2.3 (Update/Delete). Versi aktif kini mencakup 15 tabel termasuk Users dan empat tabel pesanan. Pengguna memilih pencatatan administrasi pesanan: subtotal/total otomatis, stok dan kuota terpisah. Ini menggantikan batas 10 tabel pada catatan historis berikutnya; versi `dashboard-auth/` tetap salinan sebelumnya. Permintaan penyesuaian dashboard tidak digunakan untuk mengubah sheet tim atau mengklaim commit/push/pengumpulan sudah dilakukan.

Pembaruan instruksi pengguna: buat salinan dashboard yang masih mempunyai login/logout di `dashboard-auth/`, lalu jadikan `dashboard/` versi akses langsung sebagai Administrator tanpa akun/login/logout beserta turunannya. Kedua README ditujukan kepada pengguna. Akun admin lama dan data band dipertahankan; versi langsung tidak menggunakan tabel users untuk menentukan akses. Pemisahan ini menggantikan ketentuan login pada versi `dashboard/` dalam catatan historis berikutnya. Detail dan bukti pengujian ada di [FOUNDATION_SETUP.md](FOUNDATION_SETUP.md).

Pembaruan berikutnya: isian path gambar pada versi aktif diganti upload JPG/PNG/WebP untuk anggota band dan album, disimpan di `dashboard/uploads/`, tanpa perubahan skema. Cover pada Music menggunakan resolver gambar bersama yang diperluas untuk file upload. `dashboard-auth/` dipertahankan sebagai salinan sebelumnya. Panduan pengguna upload berada di `dashboard/README.md`; kontrak validasi dan bukti pengujian terisolasi berada di [FOUNDATION_SETUP.md](FOUNDATION_SETUP.md).

Instruksi pengguna 12 November 2026, Tugas #5: kerjakan dashboard CRUD memakai template yang diberikan dalam `dashboard/`, hubungkan database band yang sudah ada, bersihkan file mahasiswa yang tidak terpakai, dan tempatkan seluruh implementasi admin di folder `dashboard` terpisah dari halaman utama. Cakupan implementasi: 10 tabel master band/konten/katalog/tour, login admin dari `users`, tanpa perubahan skema dan tanpa CRUD bebas order. Lihat [kontrak dan setup](FOUNDATION_SETUP.md#dashboard-admin--tugas-5). Snapshot jadwal di bawah tidak membatalkan instruksi langsung pengguna ini.

Dokumen ini mencatat informasi yang terkonfirmasi dan keputusan yang masih dibutuhkan. Aturan kerja ada di [RULES.md](RULES.md).

Pembaruan WBS 3.2: Home, Music, News/detail, dan Tour sudah membaca database; lihat [REPORT_3_2.md](REPORT_3_2.md). Abyan memilih Shared Playlist berupa daftar lagu bersama dari `songs` dan waktu konser WIB (`Asia/Jakarta`, UTC+7). Tidak ada tabel playlist baru. Input yang sudah diputuskan ini tidak perlu ditanyakan ulang. Catatan awal di bawah tetap merupakan riwayat sebelum implementasi.

Pembaruan setelah eksekusi WBS 3.1 pada 10 November 2026: PHP CLI 8.3.33/PDO MySQL dan MySQL Laragon 8.4.3 terverifikasi; `mcr_website` sudah tersedia dengan 15 tabel dan 11 foreign key yang definisinya cocok dengan impor skrip lokal. Koneksi bersama serta endpoint baca album telah dibuat dan diuji. UI masih statis. Lihat [laporan 3.1](REPORT_3_1.md) dan [setup backend](FOUNDATION_SETUP.md). Catatan kondisi awal dan status sheet di bawah tetap merupakan snapshot sebelum eksekusi, bukan progres terkini.

## Fakta yang terkonfirmasi

| Aspek | Informasi | Sumber |
|---|---|---|
| Kegiatan | Proyek kelompok mata kuliah Web Programming | Pernyataan Abyan |
| Tujuan | Pengembangan website My Chemical Romance, disebut “official web” dalam konteks proyek | Pernyataan Abyan dan judul jadwal |
| Backend target | PHP native | Pernyataan Abyan |
| Database target | MySQL | Pernyataan Abyan |
| Peran pengguna | Abyan, backend developer | Pernyataan Abyan |
| Implementasi lokal saat inisialisasi | Halaman HTML/CSS/JavaScript; belum ditemukan file PHP | Pemeriksaan repositori, 10 November 2026 |
| Skema lokal | `mcr_database_mysql.db` berisi skrip SQL MySQL untuk database `mcr_website`; bukan file SQLite | Pemeriksaan isi file, 10 November 2026 |

README sebelumnya menyebut “fan website”. Status afiliasi resmi dengan band belum terkonfirmasi. Jangan menyebut proyek ini sebagai situs resmi yang telah diotorisasi tanpa bukti.

## Tim dan sumber jadwal

Sumber: [Time Schedule PBL: [MCR] Official Web](https://docs.google.com/spreadsheets/d/1sAZY4DkCu79I0fbUykJCa6kNyuKLoGkHX_ZwmOo_h38/edit?gid=238306226#gid=238306226), tab **Gantt Chart**.

Jadwal dibaca melalui ekspor CSV pada **10 November 2026**. Tabel berikut adalah snapshot, bukan sinkronisasi otomatis. Periksa sheet kembali sebelum memakai status ini untuk menentukan progres terkini.

| Anggota | Peran dalam jadwal |
|---|---|
| Rafi Achmad Arlino | Project Manager & QA |
| Dimas Miftahul Arifin | Front End & UI/UX |
| Abyan Perdana Putra | Back End |
| Raffa Muhammad Shaddam | Back End |

Penanggung jawab “Back End” pada sheet belum membagi modul antara Abyan dan Shaddam. Jangan menetapkan pembagian tersebut tanpa input tim.

## Jadwal pekerjaan

Tanggal mengikuti kolom START DATE dan DUE DATE pada sheet, dalam format hari/bulan/tahun. Status disalin sebagaimana tercantum, bukan hasil pengujian kode.

| WBS | Pekerjaan | Penanggung jawab pada sheet | Mulai–selesai (2026) | Status snapshot |
|---|---|---|---|---|
| # | Pembagian kelompok dan penjadwalan | Project Manager | 23/10–30/10 | Complete |
| 1.1 | Rancangan awal kebutuhan database | All team | 23/10–26/10 | Complete |
| 1.2 | Rancangan akhir database dan relasi tabel | All team | 26/10–06/11 | Complete |
| 2.1 | Pembuatan database dan semua tabel | All team | 06/11–09/11 | Complete |
| 2.2 | INSERT dan SELECT semua tabel | All team | 09/11–13/11 | In Progress |
| 2.3 | UPDATE dan DELETE semua tabel | All team | 09/11–13/11 | In Progress |
| 3.1 | Koneksi database dan arsitektur API/backend | Back End | 14/11–15/11 | Not Started |
| 3.2 | Konten dinamis: Music, Playlist, Tour Schedule, News | Back End | 15/11–17/11 | Not Started |
| 3.3 | Katalog merch dan pemesanan tiket | Back End | 16/11–18/11 | Not Started |
| 3.4 | Form kontak, pendaftaran fans, message feed via email subs | Back End | 16/11–18/11 | Not Started |
| 3.5 | Validasi, penanganan input, error, dan revisi seluruh modul | All team | 18/11–18/11 | Not Started |
| 4.1 | Registrasi/login dengan hashing dan session | Back End | 18/11–20/11 | Not Started |
| 4.2 | Hak akses Admin dan Pengunjung/User | Back End | 18/11–20/11 | Not Started |
| 4.3 | Dashboard admin CRUD News, Tour, Merchandise | Front End | 18/11–20/11 | Not Started |
| 5.1 | Pengujian end-to-end CRUD dan transaksi | Project Manager & QA | 20/11–20/11 | Not Started |
| 5.2 | Responsivitas, performa aset, keamanan dasar (FR & EFR) | Project Manager & QA | 20/11–20/11 | Not Started |
| 5.3 | Deployment dan dokumentasi laporan PBL | Project Manager | 20/11–20/11 | Not Started |

Istilah FR & EFR dipertahankan dari sheet; kepanjangannya belum dikonfirmasi. Rincian WBS 3.4 juga perlu diperjelas sebelum menentukan skema dan layanan email.

## Implikasi untuk pengembangan

- Skema tersedia di [mcr_database_mysql.db](../mcr_database_mysql.db), berisi 15 tabel dengan InnoDB, utf8mb4, primary key, foreign key, dan sejumlah constraint. File muncul selama inisialisasi dokumentasi dan telah dibaca; belum diimpor atau diuji pada MySQL. Konfirmasi apakah ini versi final tim sebelum perubahan struktural.
- Skema mencakup anggota band, berita, pengguna, album/lagu, katalog/varian/pesanan merch, serta venue/konser/jenis tiket/pesanan tiket. Jangan mengarang tabel untuk kebutuhan lain sebelum kebutuhan dan relasinya dikonfirmasi.
- Deliverable 3.1 menyebut beberapa contoh seperti `db.php`, connection pool, atau endpoint dasar. Contoh itu tidak menetapkan nama file atau mewajibkan seluruh pendekatan; sesuaikan dengan PHP native dan kebutuhan integrasi.
- Dashboard ditugaskan ke Front End pada sheet. Kontrak data, endpoint CRUD, dan otorisasi server tetap perlu dikoordinasikan dengan backend.
- Modul katalog/pemesanan dijadwalkan sebelum autentikasi selesai. Tentukan akses guest/user/admin dan lindungi operasi administratif sejak dibuat.
- Data acara, harga, biaya, batas jumlah tiket, dan isi katalog pada prototipe belum dikonfirmasi sebagai aturan bisnis final.
- README lama memuat URL GitHub Pages dan pratinjau dengan server Python. Lingkungan PHP/MySQL serta hosting akhir masih perlu ditetapkan.

## Input yang dibutuhkan dari Abyan/tim

Isi yang sudah diketahui saja; jawaban “belum ditentukan” lebih baik daripada menebak. Jangan sertakan password, token, atau data pembayaran nyata.

### Prioritas sebelum implementasi backend

1. **Status skema:** status impor dan kecocokan teknis sudah diverifikasi pada WBS 3.1; konfirmasi apakah `mcr_database_mysql.db` adalah versi final tim sebelum perubahan struktural. Tambahkan ERD/data dummy jika tersedia; tidak perlu mengirim ulang struktur.
2. **Pembagian backend:** modul/WBS milik Abyan, modul milik Shaddam, dan bagian yang dikerjakan bersama. Ini diperlukan agar perubahan tidak bertabrakan.
3. **Lingkungan:** Laragon, PHP CLI 8.3.33/PDO MySQL, dan MySQL 8.4.3 sudah terverifikasi; target hosting dan konfigurasi Apache aplikasi belum dikonfirmasi/diuji.
4. **Ketentuan dosen:** rubrik/instruksi tugas, fitur wajib, batas library, serta format laporan/pengumpulan bila ada. Jangan menurunkannya dari contoh UI.

### Input saat mengerjakan modul terkait

- **Autentikasi:** siapa boleh mendaftar, tindakan guest/user/admin, cara membuat admin pertama, dan apakah ada akun uji.
- **Tiket/merch:** simulasi atau transaksi nyata, booking tiket saja atau juga penjualan merch, mata uang, harga/biaya, stok, batas kuantitas, status order, serta aturan pembatalan.
- **Kontak/fans/newsletter:** data yang harus disimpan, hubungan pendaftaran fans dengan akun login, arti message feed, dan apakah email sungguhan harus dikirim atau cukup disimpan untuk demo.
- **Konten/identitas:** data final dari tim, sumber aset yang boleh digunakan, serta makna “official” untuk penyajian proyek akademik ini.
- **Deployment:** hosting yang mendukung PHP/MySQL, akses yang dibutuhkan, konfigurasi nonrahasia, dan penanggung jawab deployment; jangan mengirim rahasia lewat dokumentasi.

Template jawaban yang bisa disalin:

```text
mcr_database_mysql.db versi final tim (ya/tidak/belum tahu):
Status impor MySQL / lokasi ERD atau data dummy jika tersedia:
Modul Abyan (WBS):
Modul Shaddam (WBS):
Bagian bersama:
PHP / MySQL / server lokal:
PDO MySQL tersedia (ya/tidak/belum tahu):
Ketentuan/rubrik dosen:
Aturan guest / user / admin:
Tiket dan merch (simulasi/nyata + aturan yang sudah diputuskan):
Kebutuhan kontak / fans / newsletter:
Sumber konten/aset dan status sebutan official:
Target hosting:
Hal yang belum ditentukan:
```

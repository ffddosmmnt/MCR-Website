# Daftar pekerjaan backend

Dokumen ini membagi pekerjaan backend menjadi unit yang dapat diberikan kepada agent. Baca [RULES.md](RULES.md) dan [PROJECT_CONTEXT.md](PROJECT_CONTEXT.md) sebelum memulai. Pembagian ini adalah usulan teknis berdasarkan jadwal, bukan penetapan tugas Abyan atau Shaddam. Jadwal dan status yang disalin di dokumen konteks adalah snapshot 10 November 2026; periksa sheet untuk perubahan terbaru.

## Kondisi awal yang diperiksa

Pembaruan WBS 3.1: koneksi PDO dan GET album sudah dibuat; PHP/MySQL lokal serta 15 tabel/11 foreign key `mcr_website` telah diverifikasi tanpa mutasi database tim. Lihat [REPORT_3_1.md](REPORT_3_1.md) dan [FOUNDATION_SETUP.md](FOUNDATION_SETUP.md). Daftar berikut merekam kondisi awal sebelum implementasi; progres eksekusi section 3 berada di [todo.md](todo.md).

- [mcr_database_mysql.db](../mcr_database_mysql.db) adalah skrip teks SQL MySQL dengan 15 definisi tabel. Skrip ini menamai database `mcr_website`; ekstensi `.db` tidak menjadikannya database SQLite.
- Pada 10 November 2026, sebuah layanan di `127.0.0.1:3306` dapat dihubungi dan mengirim salam protokol MySQL yang melaporkan versi `8.4.3`. Ini tidak membuktikan bahwa `mcr_website` sudah dibuat, skemanya sesuai, atau aplikasi memiliki akses. Status impor dan isi database belum diperiksa karena akses terautentikasi belum tersedia. Server bisa saja berubah setelah pemeriksaan.
- Tidak ditemukan implementasi PHP pada checkout saat pemeriksaan. Halaman, form, kalkulasi tiket, dan fan bag yang ada masih berupa frontend statis atau perilaku di browser.
- Skema mencakup `band_members`, `news_articles`, `users`, `albums`, `songs`, `categories`, `products`, `product_variants`, `merch_orders`, `merch_order_items`, `venues`, `concerts`, `ticket_types`, `ticket_orders`, dan `ticket_order_items`.
- Skema belum memuat tabel yang secara eksplisit dinamai playlist, newsletter, kontak, atau message feed. Kebutuhan dan pemetaan datanya perlu dipastikan; jangan menyimpulkan fitur tersebut tidak dapat dibuat hanya dari nama tabel.

## Cara memakai daftar ini

Setiap bagian memuat batas tugas, dependensi, dan bukti selesai. Kotak yang belum dicentang menandakan pekerjaan belum diverifikasi dalam repositori ini; bukan status resmi tim. Sebelum mengerjakan, cek file aktual, sinkronkan dengan rekan, dan tentukan pemilik perubahan untuk file bersama seperti `script.js`, navigasi, dan skema. Agent melaporkan file yang diubah, pengujian, hasil, serta input yang masih dibutuhkan.

Urutan dependensi yang disarankan: **B1 → B2 → B3**, lalu **B4–B7** sesuai kebutuhan; **B8** membutuhkan autentikasi dan aturan bisnis, sedangkan **B9** memerlukan klarifikasi. B10 berjalan bersama semua modul dan ditutup setelah integrasi. Pekerjaan yang menyentuh file yang sama perlu dikoordinasikan meski berbeda bagian.

## B1 — Verifikasi database dan lingkungan

**WBS terkait:** 2.1, 3.1. **Dependensi:** akses MySQL yang sesuai dan konfirmasi lingkungan tim. **Batas:** pemeriksaan terlebih dahulu; impor/perubahan data hanya setelah lokasi dan dampaknya dipahami.

- [ ] Pastikan versi PHP, ekstensi PDO MySQL, server web, versi server database, dan cara menjalankan proyek yang digunakan tim.
- [ ] Melalui akses terautentikasi yang diberikan tim, periksa keberadaan `mcr_website`, daftar tabel, relasi, dan perbedaan dari skrip lokal. Jangan menampilkan kredensial atau isi data pribadi.
- [ ] Konfirmasi apakah skrip lokal merupakan skema final serta apakah database yang ditemukan berisi data tim. Jika belum ada, sepakati lingkungan pengujian dan prosedur impor; jangan menimpa database yang sudah ada.
- [ ] Catat cara setup dan konfigurasi lokal yang benar-benar sudah diverifikasi, termasuk lokasi konfigurasi rahasia dan proses pemulihan bila diperlukan.

**Selesai jika:** status database dan kecocokan skema dapat dibuktikan dengan pemeriksaan yang dijalankan, atau kendala akses dan input yang diperlukan tercatat. Keberadaan port saja tidak cukup.

## B2 — Koneksi PHP dan fondasi integrasi

**WBS terkait:** 3.1. **Dependensi:** B1 untuk konfigurasi yang benar dan lingkungan yang dapat diuji.

- [ ] Buat satu konfigurasi koneksi yang digunakan bersama, memakai PDO MySQL jika tersedia, `utf8mb4`, prepared statements, dan penanganan kesalahan aman.
- [ ] Pisahkan kredensial dari kode yang dilacak dan pastikan konfigurasi rahasia tidak bisa diakses lewat web. Dokumentasikan variabel atau file konfigurasi tanpa nilai rahasia.
- [ ] Tentukan bersama frontend apakah setiap alur memerlukan render PHP atau respons JSON; catat kontrak input, output, autentikasi, dan error yang disepakati.
- [ ] Dokumentasikan perintah menjalankan PHP yang cocok dengan lingkungan yang benar-benar diuji; pertahankan jalur aset dan tautan yang relevan.

**Selesai jika:** halaman/operasi contoh dapat membaca data uji dari database yang dituju, koneksi gagal menghasilkan respons aman, dan cara setup dapat diulang tanpa membagikan rahasia.

## B3 — Autentikasi dan otorisasi

Pembaruan Tugas #5, 12 November 2026: login/logout khusus admin, hashing password melalui provisioning CLI, sesi aman, role yang dibaca ulang setiap request, dan CSRF dashboard tersedia dalam `dashboard/` serta sudah diuji. Registrasi publik dan kontrak autentikasi pengunjung belum dikerjakan; B3 keseluruhan tidak dinyatakan selesai. Lihat [setup dashboard](FOUNDATION_SETUP.md#dashboard-admin--tugas-5).

**WBS terkait:** 4.1–4.2. **Dependensi:** B2, tabel `users`, dan kesepakatan aturan akun, role, serta pembuatan admin pertama.

- [ ] Implementasikan registrasi, login, dan logout sesuai aturan tim; validasi input dan email unik, hash password, dan gunakan sesi secara aman.
- [ ] Batasi pembuatan akun publik agar tidak dapat memilih role admin. Periksa autentikasi dan role pada setiap operasi yang memerlukan akses, termasuk permintaan langsung ke endpoint.
- [ ] Sediakan perlindungan CSRF untuk perubahan data berbasis sesi dan pengaturan cookie yang sesuai lingkungan.
- [ ] Koordinasikan kontrak login dan status pengguna dengan frontend; jangan mengasumsikan tampilan admin sudah ada.

**Selesai jika:** registrasi/login/logout diuji, password tersimpan sebagai hash, sesi lama ditangani dengan benar, dan guest/user tidak dapat melakukan operasi admin. Catat hasil uji dan cara menyiapkan akun uji tanpa menyertakan password nyata.

## B4 — Musik dan konten band

**WBS terkait:** 3.2; `band_members` merupakan perluasan yang mengikuti halaman About bila dipilih tim. **Dependensi:** B2; butuh kejelasan isi playlist dan sumber konten.

- [ ] Hubungkan daftar album dan lagu ke `albums`/`songs`, termasuk relasi album–lagu dan status kosong; pertahankan pencarian yang relevan dengan halaman Music.
- [ ] Tentukan bersama tim kebutuhan playlist: skema tidak memiliki tabel playlist yang eksplisit. Jangan membuat struktur atau klaim bahwa playlist sudah tersedia sebelum disepakati.
- [ ] Jika halaman About akan dinamis, hubungkan anggota band ke `band_members`; tentukan data yang boleh digunakan.
- [ ] Escape output sesuai konteks dan tangani ID/data yang tidak ditemukan.

**Selesai jika:** konten uji ter-render dari database, pencarian/relasi bekerja sesuai kontrak, dan input atau data kosong tidak merusak halaman. Catat fitur yang masih menunggu keputusan playlist.

## B5 — News dan tour dinamis

**WBS terkait:** 3.2. **Dependensi:** B2; koordinasi dengan B7 untuk perubahan konten admin.

- [ ] Tampilkan daftar/detail berita dari `news_articles`, gunakan slug yang ada, dan tangani berita tidak ditemukan.
- [ ] Tampilkan jadwal konser dari `concerts` dan `venues`; tentukan aturan waktu, status acara, dan penampilan saat data kosong bersama tim.
- [ ] Hubungkan bagian ringkasan di halaman utama jika diperlukan; jangan mengarang tanggal, konten berita, atau jadwal konser.
- [ ] Periksa karakter khusus, link detail, dan data referensi; lakukan encoding output.

**Selesai jika:** data uji berita dan konser muncul sesuai relasi, detail yang tidak ada ditangani dengan aman, dan perubahan data yang sah tercermin pada halaman.

## B6 — Katalog merch

**WBS terkait:** 3.3. **Dependensi:** B2; aturan katalog, tampilan harga, dan varian perlu disepakati.

- [ ] Hubungkan `categories`, `products`, dan `product_variants` ke showcase; tetapkan kontrak kategori, ukuran, warna, stok, dan keadaan kosong sesuai data yang tersedia.
- [ ] Pertahankan fungsi filter yang relevan dan pastikan konten produk dinamis aman ditampilkan. Skema produk saat ini tidak memiliki kolom gambar produk yang eksplisit; sepakati sumber gambar sebelum menambah kolom.
- [ ] Jelaskan batas fan bag di browser. Simpan order hanya bila ruang lingkup dan kontrak checkout disepakati; implementasi order ada di B8.

**Selesai jika:** katalog dan filter diuji dengan produk/varian yang relevan, termasuk data kosong atau tidak ditemukan, dan harga yang ditampilkan berasal dari sumber data yang disepakati.

## B7 — Operasi CRUD dan integrasi dashboard admin

Pembaruan 13 November 2026 untuk instruksi tugas WBS 2.2/2.3: cakupan versi aktif `dashboard/` diperluas dari 10 menjadi seluruh 15 tabel. Users dan empat tabel pesanan kini memiliki Create/Read/Update/Delete. Keputusan pengguna: pesanan untuk pencatatan administrasi, subtotal/total dihitung otomatis, stok/kuota terpisah. Hasil tahap lokal dan petunjuk pengumpulan ada di README dashboard; commit/push dan pengisian kolom Deliverable belum dilakukan. Bukti lama di bawah tetap riwayat cakupan awal 10 tabel.

**WBS terkait:** 2.2–2.3, 4.3. **Dependensi:** B2–B3 dan kontrak dengan pengembang frontend dashboard. Dashboard sendiri ditugaskan ke Front End pada jadwal.

- [x] Petakan CRUD untuk seluruh 15 tabel pada versi aktif, termasuk Users serta header/item pesanan tiket dan merchandise dengan total yang konsisten.
- [x] Implementasikan News, Tour, dan Merchandise beserta master terkait dengan validasi, prepared statements, otorisasi admin, dan CSRF untuk perubahan berbasis sesi.
- [x] Tangani unique key, foreign key, data yang tidak ditemukan, konflik snapshot, dan penghapusan dengan relasi `RESTRICT`, tanpa membocorkan detail database.
- [ ] Koordinasikan nama field, format respons, dan pesan error dengan frontend. Hindari mengubah skema tanpa memastikan versi final dan dampaknya.

**Selesai jika:** operasi yang disepakati dapat dibuat, dibaca, diperbarui, dan dihapus sesuai hak akses; tes kasus valid/tidak valid, relasi, dan akses langsung tanpa izin dicatat. Jelaskan tabel atau operasi yang belum tercakup.

**Bukti Tugas #5:** run 12 November 2026 di `.runtime/foundation-d0ece45bec0b4f4ca71b9495c77158aa/dashboard-evidence.json` lulus CRUD create/read/update/delete untuk band_members, albums, songs, news_articles, venues, concerts, ticket_types, categories, products, product_variants. Update benar-benar mengubah nilai dan diperiksa pada database. Uji tambahan lulus untuk unique slug, FK/RESTRICT, ID hilang, input array/UTF-8, tanggal invalid, harga negatif, stok overflow, gambar tidak aman, pencarian literal/hasil kosong, filter relasi, sort, pagination halaman kedua, CSRF, role dicabut, anonymous/non-admin, sesi diregenerasi, logout, konflik edit/delete, dan respons 503 generik. Perubahan album/lagu/berita/konser terbaca pada Music/News/Tour. Semua mutasi memakai MySQL terisolasi, tidak menyentuh database tim. Lint 19 file PHP serta `node --check dashboard/dashboard.js` lulus; log server run tersebut tidak berisi warning/fatal/notice PHP.

Pemeriksaan browser Brave pada instance yang sama: login admin berhasil, tambah berita melalui modal menghasilkan status “Record saved.” dan baris baru, tampilan desktop terbaca, modal muat pada viewport 390×844, tabel bergulir dalam wadahnya tanpa overflow halaman (lebar dokumen 375, viewport 390), Escape menutup modal dan mengembalikan fokus ke “+ Add record”. Override viewport dikembalikan setelah uji. File backend mahasiswa/SQL/uploads kosong/metadata Git template dipindahkan dari dashboard ke cadangan privat `.runtime/dashboard-template-original/`; CSS AdminLTE asli dipertahankan. Apache belum diuji dan admin pada database tim belum dibuat otomatis; ikuti [panduan setup](FOUNDATION_SETUP.md#dashboard-admin--tugas-5).

## B8 — Pemesanan tiket dan merch

**WBS terkait:** 3.3. **Dependensi:** B2–B3, B6 untuk merch, serta keputusan aturan order dan apakah checkout simulasi atau nyata.

- [ ] Tentukan alur pemesanan, akses pengguna, status, mata uang, biaya, batas kuantitas, pembatalan, serta apakah merchandise perlu checkout. Tabel order saat ini mewajibkan `user_id`; jangan mengasumsikan guest checkout didukung skema.
- [ ] Gunakan `ticket_types`, `ticket_orders`, dan `ticket_order_items` untuk tiket; gunakan tabel order merch dan varian bila penjualan merch termasuk cakupan.
- [ ] Hitung harga dan total dari data server; validasi kuantitas, ketersediaan, dan kepemilikan. Gunakan transaksi dan perlindungan konkurensi agar kuota/stok tidak terjual melebihi batas atau tersimpan sebagian.
- [ ] Simpan data minimal sesuai kontrak yang disepakati dan batasi akses ke order milik pengguna. Jangan mengumpulkan atau menyimpan nomor kartu/CVV.
- [ ] Perbaiki alur form prototipe yang memakai GET sebelum digunakan untuk data sensitif. Integrasi pembayaran nyata membutuhkan keputusan penyedia dan persyaratan terpisah.

**Selesai jika:** order uji dapat disimpan dan dibaca oleh pihak berwenang; harga manipulatif, stok/kuota habis, kegagalan transaksi, dan permintaan bersamaan ditangani tanpa inkonsistensi. Bukti pengujian menggunakan data dummy.

## B9 — Kontak, fans, dan newsletter

**WBS terkait:** 3.4. **Dependensi:** B2 dan keputusan makna fitur, data yang dikumpulkan, serta apakah perlu email sungguhan.

- [ ] Klarifikasi hubungan pendaftaran fans dengan akun `users`, arti message feed, dan tujuan form kontak/subscription.
- [ ] Setelah kebutuhan disetujui, tentukan pemetaan data yang sesuai; bila perlu perubahan skema, dokumentasikan perubahan dan pemulihannya serta koordinasikan dengan tim.
- [ ] Implementasikan penyimpanan dan/atau pengiriman email hanya sesuai keputusan; validasi input, lindungi perubahan data dari CSRF, dan tangani kegagalan tanpa membocorkan informasi.
- [ ] Integrasikan form `news.html` dan `news-detail.html` jika keduanya memang bagian fitur yang disepakati.

**Selesai jika:** alur yang disepakati berhasil diuji dengan data dummy, input tidak valid ditolak, status gagal jelas, dan kemampuan kirim email tidak diklaim bila belum diuji.

## B10 — Verifikasi integrasi dan serah terima

**WBS terkait:** 3.5, 5.1–5.3. **Dependensi:** bagian yang akan diserahkan; koordinasi dengan Project Manager & QA.

- [ ] Uji alur end-to-end dan kasus gagal pada modul yang sudah diimplementasikan: validasi, SQL injection, output berbahaya, sesi, role, CSRF, serta integritas transaksi yang relevan.
- [ ] Uji halaman utama terkait, tautan, akses keyboard, layout desktop/mobile, dan error yang terlihat setelah integrasi frontend/backend.
- [ ] Catat hasil pengujian aktual, langkah reproduksi, data dummy, bug yang belum selesai, serta perubahan skema dan prosedur pemulihannya.
- [ ] Lengkapi dokumentasi setup berdasarkan server dan versi yang sudah diverifikasi; serahkan kebutuhan hosting dan konfigurasi nonrahasia kepada penanggung jawab deployment.

**Selesai jika:** bukti uji dan keterbatasan tiap modul tersedia, masalah yang menghalangi penggunaan diselesaikan atau diserahkan secara jelas, dan panduan setup sesuai lingkungan yang diuji. Deployment bukan bukti selesai sampai benar-benar dilakukan dan diverifikasi.

## Input yang paling menentukan langkah berikutnya

1. Apakah `mcr_database_mysql.db` merupakan skema final, dan apakah `mcr_website` sudah diimpor? Informasikan lingkungan dan cara akses yang aman tanpa menuliskan password di dokumen/chat.
2. WBS mana yang ditangani Abyan, Shaddam, dan bersama?
3. PHP/server lokal yang digunakan, serta versi MySQL yang ditargetkan oleh tim?
4. Aturan akun dan admin; checkout simulasi atau nyata; fitur fans/newsletter yang diminta; dan rubrik dosen jika tersedia.

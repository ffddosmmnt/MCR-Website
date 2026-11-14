# Rencana pengerjaan agent — WBS 3.1 sampai 3.5

> Pembaruan 14 November 2026: tampilan publik kini HTML + JavaScript dan API PHP terpisah. Rujuk [HTML_PHP_SEPARATION.md](HTML_PHP_SEPARATION.md); catatan render PHP terdahulu adalah riwayat, bukan arsitektur aktif. Hosting backend HTTPS belum ditentukan.

## Tujuan dan batas pekerjaan

Menghubungkan frontend MCR ke PHP native dan MySQL melalui potongan fitur yang dapat dijalankan serta diuji. Dokumen ini merupakan rencana, bukan laporan implementasi. Checklist eksekusi tunggal berada di [todo.md](todo.md); detail tugas dan kriterianya berada di sini.

Baca terlebih dahulu [RULES.md](RULES.md), [PROJECT_CONTEXT.md](PROJECT_CONTEXT.md), [BACKEND_TASKS.md](BACKEND_TASKS.md), dan [skema MySQL](../mcr_database_mysql.db). Ruang lingkupnya WBS 3.1–3.5. Modul login/registrasi/role pada WBS 4 dan dashboard admin bukan bagian implementasi otomatis rencana ini, tetapi autentikasi dapat menjadi dependensi pemesanan. Jangan membuka endpoint admin atau membuat user palsu untuk menghindari dependensi tersebut.

Tanggal berikut berasal dari snapshot jadwal tim yang dibaca 10 November 2026, bukan estimasi baru atau perubahan komitmen tim.

| WBS | Sasaran | Jadwal tim tahun 2026 | Tugas agent |
|---|---|---|---|
| 3.1 | Koneksi database dan fondasi integrasi | 14–15 November | T31-A, T31-B |
| 3.2 | Music, Playlist, Tour Schedule, News dinamis | 15–17 November | T32-A sampai T32-E |
| 3.3 | Katalog merch dan pemesanan tiket | 16–18 November | T33-A sampai T33-D |
| 3.4 | Kontak, pendaftaran fans, message feed/email subs | 16–18 November | T34-A sampai T34-C |
| 3.5 | Validasi, penanganan error, dan revisi | 18 November | T35-A, T35-B |

## Kondisi awal dan keputusan teknis

Pembaruan eksekusi: fondasi 3.1 dan konten 3.2 sudah selesai pada lingkungan lokal yang diuji; lihat [REPORT_3_1.md](REPORT_3_1.md), [REPORT_3_2.md](REPORT_3_2.md), [FOUNDATION_SETUP.md](FOUNDATION_SETUP.md), dan [todo.md](todo.md). Abyan memilih daftar lagu bersama untuk playlist dan WIB untuk konser. Poin kondisi awal serta pertanyaan sebelum keputusan itu dipertahankan sebagai konteks historis, bukan input yang harus ditanyakan ulang.

- Checkout saat perencanaan memuat frontend statis dan skrip SQL berisi 15 tabel; belum ditemukan implementasi PHP. Skrip `mcr_database_mysql.db` adalah teks SQL, bukan SQLite.
- Pemeriksaan sebelumnya mendeteksi salam protokol MySQL versi 8.4.3 pada port lokal 3306. Keberadaan database `mcr_website`, data di dalamnya, akses aplikasi, dan lingkungan PHP belum terverifikasi. Agent T31-A harus memeriksa ulang.
- Usulan awal: render halaman dengan PHP untuk konten yang memang menjadi dinamis; tambahkan endpoint JSON hanya bila interaksi memerlukannya. Ikuti keputusan integrasi tim jika sudah tersedia. Satu koneksi database dan helper kecil dipakai bersama; jangan membuat framework atau API generik untuk semua tabel.
- Pertahankan CSS, aset, navigasi, aksesibilitas, dan interaksi yang masih diperlukan. Konversi halaman secara bertahap; setiap perubahan ekstensi harus memperbarui tautan/action terkait agar tidak ada dua versi aktif yang menampilkan sumber data berbeda.
- Setiap operasi tulis menggunakan validasi server, prepared statements, dan perlindungan CSRF yang sesuai. Nilai harga, identitas pengguna, dan hak akses tidak diambil sebagai kebenaran dari browser.
- Tidak ada test runner atau perintah setup PHP yang sudah diverifikasi. Catat perintah aktual setelah lingkungan diketahui; gunakan pemeriksaan sintaks PHP dan uji integrasi yang sesuai, tanpa mengarang hasil.
- Gunakan database pengujian yang telah ditentukan dan data dummy. Jangan mengimpor skrip, menghapus data, atau menjalankan tes mutasi pada database tim yang belum diketahui isinya.

## Input dan dependensi yang perlu diselesaikan

| Keputusan/input | Dibutuhkan oleh | Perilaku agent jika belum ada |
|---|---|---|
| Versi PHP, ekstensi database, server web, akses lokal aman, status skema final | T31-A/B | Periksa sumber lokal terlebih dahulu; minta informasi yang belum tersedia. Jangan menebak kredensial. |
| Pemilik WBS/modul Abyan dan Shaddam | Penugasan agent | Catat pemilik sebelum mengubah modul bersama; jangan mengambil alih pekerjaan rekan. |
| Arti playlist dan sumber data | T32-E | Selesaikan album/lagu yang sudah jelas; tandai playlist menunggu keputusan. |
| Identitas pengguna untuk order dan kesiapan WBS 4.1–4.2 | T33-C/D | Katalog tetap dapat dikerjakan; jangan mengaktifkan order sampai identitas terpercaya tersedia. |
| Simulasi/nyata, mata uang, biaya, batas kuantitas, aturan kuota dan status order | T33-B/C/D | Dokumentasikan pertanyaan spesifik; jangan memakai harga/aturan prototipe sebagai keputusan tim. |
| Arti fans, kontak, message feed, subscription dan kebutuhan kirim email | T34-A/B/C | Buat pemetaan kebutuhan; jangan mengarang skema atau mengirim email nyata. |

Skema saat ini mewajibkan `user_id` pada order, belum memiliki tabel playlist/kontak/newsletter yang eksplisit, dan tidak memiliki kolom gambar produk yang eksplisit. Jika dibutuhkan perubahan struktur, minta keputusan tim mengenai perubahan data tersebut dan dokumentasikan migrasi serta pemulihan sebelum menerapkannya. Data kontak, delivery, biaya, dan timer pada prototipe tiket juga perlu dipetakan; jangan diam-diam membuang input yang dibutuhkan atau mengaku menyimpannya.

## Urutan kerja dan koordinasi agent

1. Selesaikan T31-A lalu T31-B; lakukan checkpoint fondasi.
2. Kerjakan irisan baca T32-A/B/C dan katalog T33-A setelah kontrak koneksi serta rendering stabil. Penyatuan home T32-D dilakukan setelah News dan Tour siap. T32-E menunggu keputusan playlist.
3. T33-B dan T34-A dapat mengumpulkan keputusan selama pekerjaan baca berjalan. T33-C/D menunggu kontrak order dan autentikasi; T34-B/C menunggu keputusan T34-A.
4. Jalankan validasi saat setiap fitur dibuat. T35-A/B menutup integrasi setelah semua fitur dalam cakupan tersedia; fitur yang tertunda tetap dilaporkan tertunda.

Ini adalah pembagian tugas untuk penugasan berikutnya; dokumen ini tidak menjalankan agent atau menetapkan pembagian individu tim. Bila beberapa agent ditugaskan, pemilik fondasi menangani koneksi/helper bersama, pemilik halaman menangani halaman terkait, dan satu integrator menangani navigasi serta perubahan `script.js` yang bertabrakan. Perubahan skema dilakukan berurutan oleh satu pemilik setelah disepakati. Jangan membuat koneksi atau helper serupa di masing-masing modul.

## 3.1 — Koneksi database dan fondasi backend

### T31-A — Verifikasi lingkungan dan database

**Dependensi:** tidak ada tugas sebelumnya; membutuhkan akses yang sah. **Area:** skema lokal, konfigurasi lingkungan, dokumentasi setup. **Ukuran:** satu sesi pemeriksaan.

**Langkah:** identifikasi PHP/ekstensi dan server web yang dipakai; periksa akses terautentikasi ke `mcr_website`, tabel, serta relasi; bandingkan dengan skrip lokal secara read-only. Tentukan lingkungan uji terpisah dan data dummy. Jika database belum ada, sepakati lokasi impor aman tanpa menimpa data lama.

**Kriteria selesai dan verifikasi:** versi, status akses/impor, selisih skema, serta cara menjalankan yang telah dicoba tercatat tanpa rahasia. Gunakan pemeriksaan metadata dan koneksi nyata; port terbuka tidak cukup. Jika akses belum tersedia, catat hambatan dan jangan menandainya selesai.

### T31-B — Koneksi bersama dan satu alur baca PHP

**Dependensi:** T31-A. **Area:** file koneksi/helper PHP baru sesuai pola yang dipilih, konfigurasi lokal, satu halaman yang perlu integrasi, README. **Ukuran:** satu irisan kecil; pecah lagi jika perubahan meluas.

**Langkah:** buat koneksi terpusat memakai PDO MySQL jika tersedia, charset utf8mb4, konfigurasi rahasia yang tidak dilacak/tidak dapat diunduh, serta error aman. Pilih struktur paling sederhana untuk render PHP dan definisikan kontrak data/error yang dipakai modul berikutnya. Buktikan dengan satu alur baca; jangan menambah endpoint debug publik atau connection pool tanpa kebutuhan.

**Kriteria selesai dan verifikasi:** data uji terbaca melalui PHP, koneksi gagal menghasilkan pesan aman, dan konfigurasi rahasia terlindungi. Jalankan pemeriksaan sintaks pada file PHP baru dan uji HTTP alur baca serta kegagalan koneksi pada lingkungan uji. Dokumentasikan perintah yang benar-benar dijalankan.

**Checkpoint fondasi:** modul lain dapat memakai koneksi yang sama; setup dapat diulang; tidak ada rahasia pada diff atau respons HTTP.

## 3.2 — Integrasi konten dinamis

### T32-A — Album dan lagu

**Dependensi:** T31-B. **Area:** `music.html` sebagai sumber tampilan, `albums`, `songs`, kode PHP terkait; bagian pencarian `script.js` hanya jika perlu.

**Langkah:** render album dan lagu dari relasi `songs.album_id`; pertahankan pencarian yang relevan dan akses lirik sesuai kebutuhan halaman. Tentukan kontrak filter yang digunakan agar frontend dan backend konsisten; jangan membuat layanan streaming baru.

**Kriteria/verifikasi:** album/lagu dummy muncul dari database, pencarian dan relasi sesuai kontrak, data kosong atau kata kunci tanpa hasil ditangani. Uji karakter khusus dan input pencarian tidak valid tanpa membocorkan SQL.

### T32-B — Berita dan detail

**Dependensi:** T31-B. **Area:** `news.html`, `news-detail.html`, `news_articles`, kode PHP terkait.

**Langkah:** render daftar dan detail berdasarkan `slug`; sepakati urutan tampil dan perlakuan `news_date` kosong. Gunakan konten tim atau dummy berlabel untuk uji. Jangan menganggap `content` aman untuk dirender sebagai HTML mentah.

**Kriteria/verifikasi:** slug yang ada membuka berita yang tepat; slug hilang/tidak dikenal menghasilkan respons yang sesuai; konten berkarakter HTML di-escape sesuai konteks. Uji daftar kosong dan tautan daftar ke detail. Form newsletter tetap dikerjakan pada 3.4.

### T32-C — Jadwal konser

**Dependensi:** T31-B. **Area:** `tour.html`, `concerts`, `venues`, kode PHP terkait.

**Langkah:** render relasi konser–venue, tanggal dan status; sepakati zona waktu serta aturan acara lampau/dibatalkan. Hubungkan pilihan acara/jenis tiket ke kontrak T33-B tanpa menyatakan booking sudah aktif.

**Kriteria/verifikasi:** konser dummy tampil dengan venue yang benar, tanggal/status sesuai keputusan, data kosong ditangani. Uji ID tidak dikenal dan tautan pemilihan tiket agar tidak menunjuk acara hardcoded yang berbeda.

### T32-D — Ringkasan Home dan tautan antarhalaman

**Dependensi:** T32-B/C. **Area:** `index.html`, navigasi/tautan yang terdampak konversi, kode PHP terkait.

**Langkah:** hubungkan ringkasan berita dan konser ke sumber yang sama dengan halaman detail; gunakan kembali query/helper yang sesuai. Perbarui tautan dari halaman lain setelah konversi ekstensi, termasuk halaman yang belum dinamis.

**Kriteria/verifikasi:** perubahan data uji terlihat konsisten pada Home dan halaman tujuan; tidak ada tautan rusak atau halaman statis lama yang menjadi tujuan aktif tanpa sengaja. Periksa navigasi desktop/mobile.

### T32-E — Playlist sesuai keputusan tim

**Dependensi:** T32-A dan definisi playlist yang disepakati. **Area:** Music dan pemetaan data playlist yang telah diputuskan.

**Langkah:** pastikan apakah playlist merupakan kurasi lagu, embed eksternal, atau daftar milik pengguna. Gunakan skema yang tersedia bila cocok; ajukan perubahan struktur hanya bila diperlukan. Implementasikan satu alur playlist yang benar-benar disepakati.

**Kriteria/verifikasi:** playlist memuat item sesuai sumbernya dan menangani keadaan kosong/tidak ditemukan. Jika milik pengguna, uji pembatasan pemilik. Bila definisi belum ada, laporkan T32-E tertunda dan jangan mengklaim seluruh 3.2 selesai.

**Checkpoint konten:** uji Music, News/detail, Tour, dan Home melalui server PHP; periksa encoding output, keadaan kosong, aset, serta navigasi setelah integrasi. Playlist memiliki hasil uji atau hambatan yang eksplisit.

## 3.3 — Katalog dan pemesanan

### T33-A — Katalog merch dari database

**Dependensi:** T31-B. **Area:** `showcase.html`, filter/fan bag pada `script.js`, `categories`, `products`, `product_variants`.

**Langkah:** hubungkan kategori, produk, varian, harga, stok, dan filter. Sepakati sumber gambar karena kolom gambar produk belum tersedia. Nilai harga pada browser hanya untuk tampilan; jangan menyebut fan bag sebagai order persisten.

**Kriteria/verifikasi:** katalog sesuai data uji, filter menghasilkan produk yang benar, varian stok nol dan kategori kosong ditangani. Uji nama produk berkarakter khusus agar tidak menjadi HTML aktif pada fan bag.

### T33-B — Kontrak booking dan keputusan bisnis

**Dependensi:** pembacaan skema dan kebutuhan tim; pemetaan acara dari T32-C untuk integrasi UI. **Area:** dokumentasi kontrak dan pemetaan field checkout.

**Langkah:** tetapkan input ID tiket/varian dan kuantitas, sumber identitas terpercaya, mata uang/biaya, aturan status, kuota, submit ulang, serta respons sukses/gagal. Klarifikasi apakah katalog cukup ditampilkan atau order merch wajib disimpan. Petakan kontak/delivery dari prototipe ke data yang disepakati; pembayaran nyata memerlukan keputusan penyedia tersendiri.

**Kriteria/verifikasi:** kontrak berisi contoh input/output dummy, aturan validasi dan akses, keputusan bisnis yang dikonfirmasi, serta daftar gap skema. Tinjau keterwakilan setiap field terhadap skema; jangan membuat nama endpoint seolah sudah ada. Jika auth WBS 4 belum tersedia, catat dependensi dan batas demo lokal secara eksplisit.

### T33-C — Booking tiket yang konsisten

**Dependensi:** T31-B, T33-B, dan autentikasi terpercaya dari WBS 4 atau keputusan alternatif yang disetujui tim. **Area:** checkout tiket, `ticket_types`, `ticket_orders`, `ticket_order_items`, handler PHP terkait.

**Langkah:** kirim perubahan lewat POST dengan CSRF; ambil identitas dari sumber server yang sah; hitung total dari harga server dengan presisi uang yang sesuai. Validasi acara/jenis tiket dan kuantitas; dalam satu transaksi, lindungi kuota dengan penguncian atau pembaruan bersyarat, simpan order/item, dan rollback seluruh perubahan bila gagal. Tangani submit ulang sesuai kontrak agar satu pemesanan tidak tersimpan dua kali tanpa sengaja. Batasi pembacaan hasil order ke pemilik yang sah. Nonaktifkan/hapus pengumpulan kartu/CVV pada alur aplikasi sesuai pilihan simulasi atau penyedia yang disetujui.

**Kriteria/verifikasi:** alur UI → simpan order → konfirmasi bekerja menggunakan dummy. Uji harga/ID pengguna yang dimanipulasi, stok habis, kegagalan di tengah transaksi, submit ulang, akses order pengguna lain, dan dua permintaan bersamaan memperebutkan kuota terakhir. Kuota tidak negatif dan tidak ada order sebagian. Jangan membuat akun hardcoded atau memercayai `user_id` dari form sebagai pengganti auth.

### T33-D — Order merch jika termasuk deliverable tim

**Dependensi:** T33-A/B dan autentikasi terpercaya seperti T33-C. **Area:** alur bag/checkout yang disepakati, `merch_orders`, `merch_order_items`, `product_variants`.

**Langkah:** bila wajib, simpan pesanan dan item varian dengan harga server, validasi stok, transaksi, pengamanan submit ulang dan akses pemilik. Gunakan kembali pola transaksi yang cocok; jangan memaksa tiket dan merch memakai satu struktur bila aturannya berbeda. Jika tim menetapkan showcase saja, catat keputusan dan batasnya pada laporan 3.3; jangan menghilangkan tuntutan penyimpanan katalog/order tanpa klarifikasi.

**Kriteria/verifikasi:** jika diwajibkan, pesanan dummy konsisten dan lolos uji manipulasi harga, stok terakhir bersamaan, rollback, serta akses order orang lain. Jika tidak termasuk cakupan, tersedia keputusan tim yang dapat dirujuk, bukan asumsi agent.

**Checkpoint transaksi:** jalur booking tidak dibuka tanpa identitas/akses yang benar; total dan kuota/stok ditentukan server; tidak ada data kartu/CVV dalam URL, database, log, atau dokumen uji.

## 3.4 — Kontak, fans, dan email subscription

### T34-A — Definisi fitur dan pemetaan data

**Dependensi:** kebutuhan tim; tidak harus menunggu seluruh 3.2/3.3. **Area:** dokumentasi kontrak; usulan perubahan skema jika diperlukan.

**Langkah:** pastikan data kontak yang diperlukan, apakah fans sama dengan akun `users`, makna message feed, pemilik pesan, serta apakah email hanya disimpan atau benar-benar dikirim. Petakan form yang ada di `news.html`/`news-detail.html`; identifikasi kebutuhan form yang belum ada. Usulkan skema minimal bila perlu, beserta dampak dan pemulihan, lalu minta keputusan sebelum perubahan struktural.

**Kriteria/verifikasi:** ada kontrak setiap alur dan pemetaan field/relasi yang disetujui. Tidak ada password/kredensial email pada dokumen. Jika keputusan belum tersedia, tugas downstream ditandai menunggu input.

### T34-B — Satu alur subscription lengkap

**Dependensi:** T31-B, T34-A, dan perubahan skema yang diperlukan sudah disetujui/diterapkan dengan aman. **Area:** form newsletter pada News/detail dan handler/persistensi terkait.

**Langkah:** hubungkan form ke penyimpanan sesuai kontrak; validasi email di server, POST dan CSRF yang sesuai; tangani duplikasi/pengiriman ulang serta respons gagal. Jika pengiriman email termasuk cakupan, gunakan konfigurasi aman dan uji pada penerima uji yang disepakati; jangan kirim ke daftar penerima nyata tanpa instruksi.

**Kriteria/verifikasi:** email dummy valid tersimpan sesuai kontrak, email tidak valid ditolak, duplikasi dan database gagal ditangani. UI tidak menampilkan “email terkirim” jika hanya menyimpan data. Kemampuan kirim email memerlukan bukti uji aktual bila diwajibkan.

### T34-C — Kontak, fans, dan message feed yang disepakati

**Dependensi:** T31-B, T34-A, skema yang disetujui; WBS 4 jika alur memerlukan akun. **Area:** masing-masing form/handler dan data yang telah disepakati.

**Langkah:** implementasikan satu irisan kecil per jenis form (kontak, fans, lalu message feed bila berbeda). Jika membutuhkan lebih dari satu sesi, pecah menjadi subtugas sebelum mengedit. Gunakan kembali registrasi WBS 4 bila fans berarti akun; jangan membuat sistem akun kedua. Validasi ukuran/tipe input, lindungi perubahan, dan batasi akses pesan sesuai kontrak.

**Kriteria/verifikasi:** setiap form dalam cakupan tersimpan atau terkirim sesuai kontrak, input invalid ditolak, dan pesan berisi markup tidak dieksekusi. Verifikasi akses pemilik/admin bila ada data privat. Laporkan masing-masing alur secara terpisah agar subscription yang selesai tidak menutupi form lain yang tertunda.

## 3.5 — Validasi lintas modul, penanganan error, dan revisi

### T35-A — Uji integrasi dan perbaikan terarah

**Dependensi:** irisan yang sudah selesai pada 3.1–3.4; dapat dimulai bertahap. **Area:** file modul yang gagal uji dan log hasil uji. Pecah per modul jika temuan meluas.

**Langkah:** inventarisasi input publik dan alur tulis; uji alur normal, tipe/rentang invalid, ID tidak dikenal, output berbahaya, input SQL, CSRF, serta akses tanpa izin yang relevan. Uji database tidak tersedia, respons aman, relasi, dan konsistensi transaksi; perbaiki akar masalah pada pemiliknya. Lakukan regresi pada fitur yang terdampak, bukan menambah lapisan sanitasi yang sama di banyak tempat.

**Kriteria/verifikasi:** setiap kasus memiliki langkah, hasil yang diharapkan, hasil aktual, dan bukti tanpa rahasia. Perbaikan diuji ulang secara fokus; kasus stok/kuota bersamaan benar-benar dijalankan bila transaksi diaktifkan. Fitur yang belum bisa diuji dicatat sebagai belum terverifikasi, bukan lulus.

### T35-B — Penutupan section 3 dan serah terima

**Dependensi:** T35-A dan keputusan atas fitur tertunda. **Area:** README, dokumentasi setup/kontrak/hasil uji, checklist ini; perbaikan tautan atau UI yang terbukti bermasalah.

**Langkah:** uji alur utama melalui UI dan navigasi desktop/mobile setelah konversi PHP; verifikasi label, fokus, dan pesan status/error. Perbarui instruksi setup, kebutuhan konfigurasi, perubahan skema/pemulihan, dependensi WBS 4, serta catatan serah terima kepada frontend dan QA. Jalankan pemeriksaan sintaks PHP yang relevan dan pemeriksaan diff.

**Kriteria/verifikasi:** tiap WBS memiliki ringkasan implementasi dan hasil uji; tidak ada tautan aktif rusak, rahasia pada perubahan, atau klaim fitur yang belum bekerja. 3.1–3.5 dinyatakan selesai hanya jika semua deliverable yang disepakati terpenuhi; bila masih bergantung input/auth, tandai sebagian selesai dan sebutkan hambatannya. Deployment akhir tetap mengikuti WBS 5.

## Format penugasan dan laporan agent

Salin template berikut untuk satu tugas kecil; pilih ID yang dependensinya sudah terpenuhi. Nama file PHP yang belum ada ditentukan saat implementasi sesuai struktur aktual, bukan dianggap tersedia oleh rencana ini.

```text
Kerjakan tugas [ID] pada agent/plan.md dan lacak hasil di agent/todo.md.
Baca AGENTS.md, kontrak yang sudah ada, dan kode terbaru sebelum mengedit.
Periksa dependensi; gunakan implementasi rekan yang sudah tersedia.
Implementasikan hanya cakupan tugas tersebut, jalankan verifikasi yang relevan,
dan laporkan input material yang belum ada tanpa mengarang keputusan tim.
Jika terhambat, lanjutkan hanya bagian independen; jangan melewati otorisasi
atau membuat skema tandingan untuk menghindari hambatan.

Laporan:
- ID tugas dan status (selesai/sebagian/menunggu input).
- File yang berubah dan perilaku yang sekarang bekerja.
- Pemeriksaan/perintah yang dijalankan beserta hasil aktual.
- Perubahan kontrak/skema dan koordinasi yang diperlukan.
- Input/dependensi yang belum selesai dan langkah berikutnya.
```

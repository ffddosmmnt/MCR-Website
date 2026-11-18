# Isi database dan menjalankan proyek di perangkat lain

Paket ini berisi **konten awal proyek kuliah**, bukan salinan database pribadi. Venue, jadwal konser 2027, jenis/harga tiket, merchandise, harga/stok dan pesan fans adalah **data contoh fiktif yang disetujui pengguna**, bukan informasi resmi band atau testimoni nyata. Jangan memakainya untuk menjual tiket/barang sungguhan. Penjelasan ini disimpan di dokumentasi; antarmuka memakai nama/status biasa sesuai keputusan proyek.

## Berkas yang dibagikan

| Berkas | Fungsi |
|---|---|
| [mcr_database_mysql.db](../mcr_database_mysql.db) | Skema awal 15 tabel; isinya SQL MySQL walaupun ekstensi .db. |
| [001_checkout_requests.sql](../migrations/001_checkout_requests.sql) | Tabel pencegah duplikasi checkout. |
| [002_interactions.sql](../migrations/002_interactions.sql) | Tabel kontak privat, subscription dan pesan fans publik. |
| [003_product_images.sql](../migrations/003_product_images.sql) | Kolom gambar opsional pada produk yang sudah ada. |
| [content.sql](content.sql) | Konten awal dengan relasi berdasarkan nama/slug; tidak memakai ID lokal yang tetap. |
| [import.php](import.php) | Importer CLI dengan transaksi, pengecekan tabel InnoDB, kunci impor dan ringkasan penambahan per tabel. |
| [assets/](../assets/) | Gambar yang dirujuk menggunakan path relatif; wajib ikut disalin. |

Tidak menyertakan users, password/hash, pesanan, checkout_requests, kontak privat, daftar subscriber, sesi, atau konfigurasi rahasia. Akun dibuat sendiri pada setiap perangkat. Jangan membagikan dump database aktif untuk menggantikan paket ini.

## Isi yang tersedia

| Tabel | Record baru pada database kosong | Peruntukan |
|---|---:|---|
| band_members | 4 | Data nama/instrumen tersedia untuk admin; About masih memakai markup anggota statis. |
| albums | 4 | Empat album studio 2002–2010. |
| songs | 20 | Pilihan lima judul per album; lirik Welcome to the Black Parade dan Na Na Na diisi dari teks yang diberikan pengguna pada 18 November 2026, 18 lagu lainnya masih NULL. Ini bukan seluruh diskografi/tracklist. |
| news_articles | 9 | Enam artikel editorial proyek dan tiga ringkasan berita bersumber internet. Tanggal record adalah tanggal kurasi proyek; tanggal sumber asli dinyatakan dalam isi. |
| venues | 3 | Nama tempat fiktif di Jakarta, Bandung dan Surabaya. |
| concerts | 4 | Tiga scheduled dan satu cancelled, Mei–Juni 2027. |
| ticket_types | 8 | General Admission/Balcony, harga IDR dan kuota contoh. |
| categories | 4 | Tees, Outerwear, Accessories, Prints. |
| products | 8 | Produk/deskripsi/harga contoh, tanpa klaim katalog resmi. |
| product_variants | 18 | Ukuran/warna/stok, termasuk satu varian habis. |
| fan_messages | 3 | Pesan orisinal dengan nama pena fiktif; ganti dengan pesan yang benar-benar dipilih admin nanti. |

Total **85 record**, di 11 tabel konten, tanpa tabel baru. Berita/albums/lagu/katalog/konser/feed memakai API yang sudah ada. Hero, teks sejarah, artwork editorial dan sebagian blok halaman tetap HTML; impor ini tidak membuat seluruh halaman bisa diedit dari database.

## 1. Persiapan perangkat baru

Salin/clone seluruh kode dan assets. Gunakan PHP dengan PDO MySQL dan mbstring; GD diperlukan untuk fitur unggah gambar dashboard. Lingkungan yang diuji: PHP 8.3.33 dan MySQL 8.4.3. Versi lain belum diklaim terverifikasi. Aktifkan MySQL Laragon terlebih dahulu. GitHub Pages sendiri tidak menjalankan PHP/MySQL.

Untuk instalasi baru, melalui HeidiSQL/phpMyAdmin/MySQL client:

1. Impor mcr_database_mysql.db sebagai SQL; file membuat/memilih mcr_website. Pada file picker, pilih semua tipe file jika ekstensi .db tidak terlihat.
2. Pilih database mcr_website, impor migrations/001_checkout_requests.sql.
3. Pada database yang sama, impor migrations/002_interactions.sql.
4. Impor migrations/003_product_images.sql sebelum mengimpor konten.
5. Pastikan 19 tabel tersedia, lalu lanjutkan impor konten di bawah.

Di MySQL client, dari root proyek, alternatif perintah impor skema adalah:

```text
mysql --user=YOUR_DB_USER --password --execute="source mcr_database_mysql.db"
mysql --user=YOUR_DB_USER --password mcr_website --execute="source migrations/001_checkout_requests.sql"
mysql --user=YOUR_DB_USER --password mcr_website --execute="source migrations/002_interactions.sql"
mysql --user=YOUR_DB_USER --password mcr_website --execute="source migrations/003_product_images.sql"
```

Ganti YOUR_DB_USER sesuai akun lokal; password diminta oleh client. Gunakan host/port sesuai instalasi. Perintah di atas mengasumsikan mysql dan php tersedia di PATH Laragon/terminal. Path executable yang memuat spasi di PowerShell dipanggil dengan operator &.

Untuk instalasi yang sudah lengkap, **jangan hapus atau impor ulang database**; langsung gunakan importer konten. IF NOT EXISTS pada skema bukan pembaruan otomatis untuk definisi tabel yang sudah berbeda. Buat cadangan pribadi jika database sudah berisi data penting, dan jangan commit cadangan tersebut.

## 2. Konfigurasi dan impor konten

Contoh terminal PowerShell 7.5+ dari root proyek:

```powershell
$env:DB_HOST = '127.0.0.1'
$env:DB_PORT = '3306'
$env:DB_NAME = 'mcr_website'
$env:DB_USER = Read-Host 'MySQL user'
$env:DB_PASSWORD = Read-Host 'MySQL password (Enter jika kosong)' -MaskInput
php database/import.php --dry-run
php database/import.php
php -d display_errors=0 -S 127.0.0.1:8000 router.php
```

Periksa nama database dan ringkasan dry-run sebelum impor. Dry-run melakukan rollback record; counter AUTO_INCREMENT dapat tetap maju, sehingga ID tidak harus berurutan. Mode rolled_back berarti belum ada konten baru yang disimpan; committed berarti penambahan disimpan. Pada database kosong jumlah inserted sesuai tabel di atas; pada impor ulang seharusnya seluruh inserted bernilai 0. Perbaikan path gambar tidak menambah record sehingga tidak tercatat sebagai inserted; --dry-run juga membatalkan perubahan gambar.

Windows PowerShell lama dapat menghapus environment variable saat nilainya kosong. Untuk password kosong, gunakan PowerShell 7.5+ seperti contoh, atau atur environment kosong langsung pada proses PHP. Jangan mengganti app/database.php dengan kredensial hardcode. Berkas .env tidak dimuat otomatis. Pengelola hosting harus memasang DB_* pada proses PHP di servernya.

Jalankan skrip dari terminal, bukan URL browser. Importer menggunakan database() milik aplikasi, memerlukan SELECT/INSERT serta akses metadata tabel dan GET_LOCK. Ia tidak membuat skema, akun MySQL, atau akun aplikasi. Semua tabel yang diperlukan wajib InnoDB. Proses impor serentak melalui importer yang sama diserialisasi. Hindari mengedit katalog melalui dashboard selama impor berlangsung.

Alternatif SQL client: pilih database yang benar, jalankan content.sql sebagai satu transaksi dan hentikan saat error; lakukan ROLLBACK jika editor masih membuka transaksi. **CLI import.php direkomendasikan** karena memastikan rollback seluruh penambahan ketika ada kesalahan. SQL tidak menonaktifkan foreign key dan tidak menjalankan DELETE/TRUNCATE. UPDATE hanya mengisi image_path produk yang NULL atau mengganti cover_image/cover_img yang NULL atau cocok dengan path pengganti lama yang ditentukan, bukan stok/harga atau gambar custom lain.

## 3. Akun pada perangkat baru

Buat Fan Account melalui account.html. Tidak ada akun/password bawaan dari seed. Akun admin disiapkan pengelola dengan provisioning CLI yang sudah tersedia di dashboard-auth/internal/create-admin.php; nama/email sebagai argumen dan password 12–72 byte melalui stdin. Gunakan password baru milik perangkat tersebut dan jangan simpan di repositori. Proses ini membuat admin baru, bukan mengubah akun lama.

Contoh PowerShell 7.5+ (DB_* sudah disetel):

```powershell
$adminName = Read-Host 'Nama admin'
$adminEmail = Read-Host 'Email admin'
Read-Host 'Password admin baru (12-72 byte)' -MaskInput | php dashboard-auth/internal/create-admin.php $adminName $adminEmail
```

Login dashboard melalui /dashboard/login.php; sesi pengunjung dan admin terpisah. Jangan mencatat password pada dokumen atau pesan commit.

## 4. Membuktikan hasil impor

Buka http://127.0.0.1:8000/ pada server PHP yang berjalan:

- Home: tiga ringkasan berita dan jadwal konser.
- Music: empat album, 20 judul lagu, pencarian judul/album; lirik yang belum tersedia menampilkan pesan bawaan.
- News: enam artikel; buka detail, termasuk news-detail.html?slug=explore-four-chapters. Tiga pesan fans muncul pada feed.
- Tour: empat konser. Konser pertama 15 Mei 2027, **19.30 WIB** (SQL menyimpan 12.30 UTC). Satu konser berstatus cancelled. Pilih konser scheduled di masa depan untuk menguji pemesanan.
- Store: menampilkan empat produk pertama menurut ID, dengan foto; database tetap menyimpan empat kategori, delapan produk dan 18 varian; After Midnight Crewneck XL/Charcoal stok nol tidak bisa dipilih. Harga menggunakan IDR.
- Daftar Fan Account, pilih tiket/merch, review total, buat order pending. Maksimal dua tiket per akun per konser; stok/kuota berkurang saat order dibuat. Pembayaran dan pembatalan tetap belum diintegrasikan.
- Jalankan importer lagi setelah stok berkurang: stok dan edit admin pada record yang cocok harus tetap; seed tidak mengisi ulang inventori.

Tanggal konser tetap, tidak bergeser otomatis mengikuti tanggal impor. Setelah konser lewat, ubah melalui dashboard untuk pengujian berikutnya; jangan menyebut jadwal ini sebagai jadwal band nyata. Konfigurasi site-config.js kosong memakai API pada host yang sama. Untuk GitHub Pages perlu backend HTTPS terpisah; ubah apiBase sesuai host backend, bukan alamat MySQL.

## Impor ulang dan relasi

Pencocokan mempertahankan record yang sudah ada: album berdasarkan judul; lagu berdasarkan album+judul; venue berdasarkan nama+kota+negara; konser berdasarkan venue+waktu UTC; jenis tiket berdasarkan konser+nama; kategori berdasarkan nama; produk berdasarkan kategori+nama; varian berdasarkan produk+ukuran+warna; berita berdasarkan slug; anggota berdasarkan nama; pesan fans berdasarkan nama publik+isi. Nilai lama tidak ditimpa, kecuali path gambar pengganti yang dikenal diperbarui ke foto/cover asli.

Jika key pencocokan diubah lewat dashboard, impor ulang dapat membuat record awal lagi. Impor konten adalah langkah instalasi, bukan sinkronisasi atau pemulihan edit. Jangan mengubah nama/slug contoh di SQL lalu mengharapkan record lama dihapus. Parent dengan nama yang sama lebih dari satu dapat membuat subquery ambigu; importer gagal dan rollback agar tidak memilih relasi secara sembarang. Periksa duplikasi lewat dashboard sebelum menjalankan ulang. File SQL menggunakan transaksi; tidak ada reset otomatis.

## Foto asli, cover dan bagian yang masih bisa diganti

Empat cover album sekarang memakai artwork asli katalog Apple Music dalam assets/albums/, dan About memakai foto panggung asli Gerard Way, Ray Toro, Frank Iero dan Mikey Way dalam assets/members/. Path yang sama diisi pada band_members untuk dashboard. Sumber, lisensi dan daftar berkas: [IMAGE_CREDITS.md](../assets/IMAGE_CREDITS.md). Tidak ada gambar AI baru atau edit pada berkas sumber.

Untuk database yang sudah diisi versi awal, cukup jalankan importer lagi. Ia mengganti gambar NULL/path pengganti lama yang dikenali dan menambahkan tiga berita baru, tanpa mengubah gambar custom lain, stok/kuota, akun atau pesanan. Aset lama tidak dihapus. Jika sebelumnya sudah memakai gambar custom, ganti melalui dashboard bila memang ingin memakai gambar baru; importer sengaja mempertahankannya.

Yang masih dapat diganti:

- Foto Ray Toro mempunyai resolusi sumber lebih rendah; dapat diganti foto asli lain dengan sumber/izin jelas.
- Hero Home/Tour/Store dan ilustrasi editorial tetap aset sebelumnya, belum seluruhnya foto band asli.
- Empat produk awal memakai foto representatif toko resmi MCR di assets/products/. Nama/harga/stok tetap konten kuliah. Ganti lewat Products → Edit → Product image pada dashboard utama; gambar custom tidak ditimpa importer.
- Gambar per lagu/single belum tersedia; setiap lagu tetap terkait dengan album dan artwork albumnya, tanpa kolom gambar lagu baru.
- Materi final tanggal/venue/harga/kuota konser, katalog/harga/stok produk, pesan fans yang diizinkan dan lirik tetap memerlukan input tim. Berita faktual tidak otomatis membuat event/tiket contoh menjadi resmi.

Untuk foto album pada perangkat yang berjalan gunakan Albums → Edit → unggah gambar. File upload dashboard/uploads tidak dilacak Git dan tidak otomatis ikut clone. Untuk aset bersama lintas perangkat, simpan gambar final di assets/ dengan nama stabil dan perbarui record secara sengaja. Cover asli tetap berhak cipta; foto Eastscene diikuti sebagai CC BY-NC 2.0 dan foto Ray CC0. Rincian serta perbedaan metadata lisensi dicatat pada credits.

## Berita internet — diperiksa 18 November 2026

Ketiga ringkasan ditulis ulang secara singkat, bukan menyalin artikel penuh. Sumber dapat diklik pada detail berita; teks database tetap dirender sebagai teks aman. Tidak ada scraping otomatis berjadwal.

| Slug | Fakta yang dirangkum | Sumber |
|---|---|---|
| mcr-live-jakarta-22-november-2026 | Jadwal 22 November 2026 di Jakarta International Stadium, sesuai poster promotor. Jam mulai pertunjukan tidak ditebak. | [MCR Jakarta / Ravel Entertainment](https://www.mcrjakarta.com/), [poster resmi](https://www.mcrjakarta.com/mcr1.jpg?key=MCR_JAKARTA2026). |
| gerard-way-recording-plans-2027 | Pernyataan mengenai keinginan merekam pada 2027; tidak diklaim sebagai album selesai atau tanggal rilis pasti. | [Simon Young, Metal Hammer/Louder, 2 November 2026](https://www.loudersound.com/bands-artists/my-chemical-romance-to-work-on-album-2027). |
| mcr-prelude-to-the-end-short-film | Film pendek Prelude to the End bersama Lauren Tsai dan jajaran pengisi suara yang dilaporkan. | [Hattie Lindert, Pitchfork, 29 Oktober 2026](https://pitchfork.com/story/phoebe-bridgers-and-billie-eilish-are-in-my-chemical-romances-new-short-film/), [unggahan YouTube resmi](https://www.youtube.com/watch?v=BpJikRTDqdk). |

Tanggal news_date pada tiga record adalah tanggal kurasi lokal **18 November 2026**, sehingga muncul di atas editorial awal. Tanggal sumber asli dan waktu pemeriksaan dinyatakan dalam isi, tanpa mengaku bahwa sumber menerbitkan semuanya pada hari yang sama. Informasi dapat berubah; periksa kembali promotor sebelum dipakai untuk rencana hadir. Tidak memasukkan jadwal nyata tersebut ke checkout/ticket_types: venue/konser/tiket pada seed tetap contoh kuliah.

## Sumber metadata

Diperiksa 18 November 2026; teks artikel ditulis sendiri untuk proyek. Judul/tanggal rilis album mengikuti katalog berikut (tanggal rilis dapat berbeda per pasar/edisi):

- [Bullets — Apple Music](https://music.apple.com/us/album/i-brought-you-my-bullets-you-brought-me-your-love/1157609234), 23 Juli 2002.
- [Three Cheers — Apple Music](https://music.apple.com/us/album/three-cheers-for-sweet-revenge/1156311431), 8 Juni 2004.
- [The Black Parade — Apple Music](https://music.apple.com/us/album/the-black-parade/209388277), 23 Oktober 2006 pada katalog yang dirujuk.
- [Danger Days — Apple Music](https://music.apple.com/us/album/danger-days-the-true-lives-of-the-fabulous-killjoys/396793722), 22 November 2010.
- [Video resmi band](https://www.mychemicalromance.com/media/videos), referensi judul video/lagu.

Tidak mengambil lirik atau review dari sumber metadata tersebut. Cover memakai artwork katalog sesuai credits; ringkasan berita memakai sumber terpisah di atas. Venue/konser/harga/produk/testimoni pada seed tidak berasal dari sumber resmi.

## Gambar dan batas Store

Migrasi 003 menambahkan kolom nullable products.image_path, tanpa tabel baru. Terapkan sekali pada instalasi lama sebelum menjalankan API/dashboard/importer versi ini. Jalankan importer untuk mengisi empat path foto yang masih NULL. Store/API menampilkan maksimal empat produk dengan ID terkecil; kategori filter mengikuti produk tersebut. Produk lain tidak dihapus, tetap bisa dikelola admin, dan order lama tetap utuh.

Untuk membatalkan tampilan, kembalikan kode API/UI terlebih dahulu. Kolom gambar boleh tetap ada; jangan menghapus kolom sebelum menyimpan seluruh path gambar yang diperlukan. Foto hanya boleh mengacu ke aset lokal atau upload yang lolos resolver, bukan URL eksternal.

## Lirik yang diberikan pengguna

Welcome to the Black Parade (album The Black Parade) memakai teks 70 baris yang dikirim pengguna pada 18 November 2026. Seed mengisi lirik hanya jika NULL atau kosong, sehingga impor ulang mempertahankan revisi admin. Tidak ada perubahan skema. Ini bukan klaim lisensi bebas atas lirik. Untuk perubahan berikutnya gunakan dashboard Songs.

Na Na Na (Na Na Na Na Na Na Na Na Na), album Danger Days: The True Lives of the Fabulous Killjoys, juga memakai teks pengguna (63 baris). Pesan pengguna berisi salinan identik dua kali; hanya satu salinan disimpan. Aturan seed yang sama mempertahankan lirik admin yang sudah terisi.

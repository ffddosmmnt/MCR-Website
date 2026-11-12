# Panduan Pengguna MCR Admin — Akses Langsung

MCR Admin digunakan untuk menambah, melihat, mengubah, dan menghapus informasi band, musik, berita, konser, serta merchandise.

## Membuka dashboard

1. Buka [dashboard lokal](http://127.0.0.1:8000/dashboard/) atau alamat dashboard yang diberikan pengelola aplikasi.
2. Halaman pengelolaan langsung terbuka sebagai **Administrator**.
3. Pilih menu data yang ingin dikelola.

Versi ini tidak memerlukan akun, email, atau password dan tidak memiliki tombol login maupun logout. Siapa pun yang membuka halaman ini dapat menggunakan seluruh fungsi pengelolaan. Untuk selesai, cukup tutup tab dashboard.

Perubahan yang Anda simpan berlaku pada data band yang digunakan aplikasi. Hindari mengubah atau menghapus konten yang masih digunakan.

## Mengenal menu

Pilih salah satu menu di bagian atas halaman sesuai data yang ingin dikelola.

| Menu | Kegunaan |
|---|---|
| **Band members** | Mengelola nama anggota band, instrumen, dan gambar anggota. |
| **Albums** | Mengelola judul album, tanggal rilis, dan gambar sampul. |
| **Songs** | Mengelola judul lagu dan lirik, serta memilih album lagu tersebut. |
| **News** | Mengelola judul, alamat singkat berita, isi, dan tanggal berita. |
| **Venues** | Mengelola nama tempat konser, kota, dan negara. |
| **Concerts** | Mengelola tempat, tanggal, waktu, dan status konser. |
| **Ticket types** | Mengelola jenis tiket, harga, dan kuota untuk suatu konser. |
| **Categories** | Mengelola kategori merchandise. |
| **Products** | Mengelola nama produk, kategori, harga, dan deskripsi. |
| **Product variants** | Mengelola pilihan ukuran, warna, dan stok suatu produk. |

Beberapa pilihan memerlukan data lain yang sudah tersedia. Tambahkan album sebelum memasukkan lagu, tempat konser sebelum membuat konser, dan konser sebelum memasukkan jenis tiket. Untuk merchandise, buat kategori terlebih dahulu, lalu produk, kemudian pilihan ukuran atau warnanya.

## Menambah data

1. Pilih menu yang sesuai, misalnya **Albums**.
2. Klik **+ Add record**.
3. Isi formulir yang muncul. Isian bertanda **(optional)** boleh dikosongkan.
4. Jika ada daftar pilihan, pilih data yang sesuai, misalnya album untuk sebuah lagu.
5. Untuk gambar anggota band atau sampul album, klik **Choose File / Pilih File** pada **Image** atau **Cover image**, lalu pilih gambar dari perangkat Anda. Bagian ini boleh dikosongkan.
6. Klik **Save record**.

Pesan **Record saved.** menandakan data berhasil disimpan. Jika ada pesan kesalahan, perbaiki isian yang disebutkan lalu simpan kembali.

Untuk membatalkan pengisian, klik **Cancel**, tombol **×**, atau tekan **Escape**.

## Melihat dan mencari data

Setelah memilih menu, data ditampilkan dalam daftar. Klik ringkasan lirik, isi berita, atau deskripsi produk untuk membaca teks lengkapnya.

| Kontrol | Cara menggunakan |
|---|---|
| **Search records** | Ketik kata yang ingin dicari, lalu klik **Apply**. |
| Pilihan album, tempat konser, kategori, produk, atau konser | Pilih salah satu untuk menampilkan data terkait, lalu klik **Apply**. Pilih **All** untuk menampilkan semuanya. |
| **Rows per page** | Pilih 5, 10, atau 15 baris, lalu klik **Apply**. |
| Judul kolom | Klik untuk mengurutkan data. Klik kembali untuk membalik urutan. Panah menunjukkan arah urutan. |
| **First / Previous / Next / Last** | Berpindah ke halaman pertama, sebelumnya, berikutnya, atau terakhir. |
| Nomor halaman | Klik nomor halaman yang ingin dibuka. |
| **Go to page** | Isi nomor halaman, lalu klik **Go**. |
| **Reset** | Mengembalikan tampilan daftar ke pengaturan awal, termasuk menghapus pencarian dan pilihan penyaring. |

Pencarian berlaku pada menu yang sedang dibuka. Untuk mencari lagu dari album tertentu, gunakan pilihan album di menu **Songs**.

Pesan **No records found.** berarti belum ada data atau tidak ada hasil yang cocok. Coba kata lain atau klik **Reset**. Jika menggunakan ponsel, geser area daftar ke samping untuk melihat kolom yang belum terlihat.

## Mengubah data

1. Temukan data yang ingin diubah.
2. Klik **Edit** pada baris tersebut.
3. Periksa kembali data yang dipilih, lalu ubah isian yang diperlukan.
4. Klik **Save record**.

Perubahan berhasil disimpan setelah muncul pesan **Record saved.**

Untuk mengganti gambar anggota band atau sampul album, pilih file baru saat membuka **Edit**. Jika tidak memilih file baru, gambar sebelumnya tetap digunakan. Gambar yang tersimpan dapat dilihat pada daftar data dan formulir edit.

Jika data sudah diubah oleh admin lain, muat ulang halaman dan periksa versi terbarunya sebelum mengubah kembali.

## Menghapus data

1. Temukan data yang ingin dihapus.
2. Klik **Delete** pada baris tersebut.
3. Periksa nama atau keterangan data pada kotak konfirmasi.
4. Klik **Yes, delete** untuk melanjutkan, atau **Cancel** untuk membatalkan.

**Data yang sudah dihapus tidak dapat dikembalikan melalui dashboard.**

Data yang masih digunakan oleh informasi lain tidak dapat dihapus. Contohnya, album yang masih memiliki lagu tidak dapat dihapus. Periksa data yang terkait terlebih dahulu. Jika tetap diperlukan untuk catatan pesanan atau informasi lainnya, pertahankan data tersebut dan hubungi pengelola aplikasi.

## Petunjuk mengisi formulir

- **Slug** adalah nama singkat yang digunakan pada alamat berita, misalnya `kabar-tur-baru`. Gunakan huruf kecil, angka, dan tanda hubung antarkata. Setiap berita harus memiliki slug yang berbeda.
- **Harga** diisi tanpa simbol mata uang atau pemisah ribuan. Contoh: `125000` atau `125000.00`. Harga tidak boleh negatif dan dapat memiliki paling banyak dua angka di belakang titik.
- **Stok dan kuota** diisi dengan bilangan bulat nol atau lebih, tanpa angka pecahan.
- **Waktu konser** menggunakan WIB. Periksa tanggal dan jam sebelum menyimpan.
- **Image / Cover image** diunggah langsung dari perangkat, tanpa mengetik lokasi file. Pilih gambar **JPG, PNG, atau WebP**, maksimal **2 MB**, dengan panjang/lebar maksimal **6000 piksel** dan luas maksimal **20 megapiksel**. Jika formulir gagal disimpan, pilih kembali file gambar sebelum mencoba lagi.
- **Lirik, isi berita, dan deskripsi produk** diisi sebagai teks biasa. Gunakan baris baru untuk memisahkan bagian tulisan.

## Penyimpanan gambar

Gambar yang berhasil diunggah otomatis disimpan di folder **dashboard/uploads** dengan nama yang dibuat oleh aplikasi. Anda tidak perlu menyalin file ke folder tersebut atau mengisi alamat gambar sendiri.

Mengganti gambar atau menghapus data tidak menghapus berkas gambar yang sudah tersimpan. Hal ini menjaga gambar lama tetap tersedia bila masih diperlukan. Fitur unggah tersedia pada **Band members** dan **Albums**.

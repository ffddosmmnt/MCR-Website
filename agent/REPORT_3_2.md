# Laporan hasil WBS 3.2 — Konten dinamis

Tanggal: 10 November 2026, Asia/Jakarta. Pelaksanaan mengikuti instruksi Abyan pada chat ini. Semua data dummy, perubahan data untuk uji, dan screenshot berisi data uji berasal dari instance MySQL terpisah; database tim tidak diisi atau diubah.

## Status dan keputusan pengguna

**T32-A sampai T32-E dan checkpoint konten selesai pada server development PHP yang diuji.**

- Playlist dipilih sebagai daftar lagu bersama dari tabel `songs`, bukan playlist kurasi baru atau playlist pribadi. Semua pengunjung membaca katalog yang sama; tidak ada perubahan skema, akun, atau penyimpanan pilihan pribadi.
- Zona waktu konser dipilih pengguna: WIB / `Asia/Jakarta` (UTC+7). Koneksi PDO membaca TIMESTAMP dalam UTC, lalu tampilan mengonversinya ke WIB. Contoh fixture `2026-11-17 13:00:00` UTC tampil sebagai `17 Nov 2026, 20:00 WIB`.
- Status acara ditampilkan persis dari database. Belum ada keputusan bisnis yang menjadikan suatu status sebagai bukti tiket tersedia; tombol hanya membuka detail acara.

## Perubahan per tugas

| Tugas | Hasil |
|---|---|
| T32-A | `music.php` membaca album dan lagu beserta relasi album. Pencarian mencakup judul, album, dan isi lirik; query GET divalidasi dan tetap bekerja sebagai filter awal tanpa JS. Link Read Lyrics membuka rincian lagu. |
| T32-B | `news.php` membaca berita terbaru; `news-detail.php?slug=...` membaca artikel tepat berdasarkan slug, menampilkan isi sebagai teks aman, serta berita lain yang relevan sebagai navigasi. |
| T32-C | `tour.php` membaca konser dan venue, menangani tanggal kosong, memakai WIB, dan menyediakan detail acara melalui `?concert=<id>`. ID invalid menghasilkan 400; ID tidak ditemukan 404. |
| T32-D | `index.php` memakai sumber query berita/konser yang sama; perubahan data uji tercermin pada Home, News, dan detail tanpa mengedit markup. Navigasi halaman statis yang tersisa diperbarui. |
| T32-E | Bagian Shared Playlist membaca daftar lagu bersama dan liriknya, dengan keadaan kosong serta keterangan bahwa audio per lagu belum terhubung. |

Lima halaman `.html` terkait diganti menjadi `.php`, bukan dipertahankan sebagai versi aktif kedua. Router mengalihkan URL lama dengan 308, mempertahankan query seperti slug; `.htaccess` menyediakan aturan setara untuk Apache. Uji HTTP dan browser memakai router PHP; Apache tetap perlu diverifikasi secara terpisah.

## Struktur dan kontrak implementasi

- [app/content.php](../app/content.php) memiliki query bersama untuk album, lagu, berita, slug artikel, dan konser/venue. Endpoint album 3.1 memakai query album yang sama, sehingga tidak ada dua definisi pembacaan album yang berbeda.
- [app/view.php](../app/view.php) dan template header/footer di `app/views/` menangani encoding HTML, pemuatan konten dengan respons 503 aman, gambar lokal yang diizinkan/fallback, format tanggal, serta navigasi halaman dinamis.
- Daftar utama dibatasi maksimum 100 per jenis konten; Home mengambil tiga berita dan tiga konser. Urutan album/berita: tanggal terbaru lalu ID turun; tanggal null mengikuti tanggal yang ada. Lagu diurutkan berdasarkan judul album, judul lagu, lalu ID. Konser diurutkan kronologis, tanggal null terakhir. Belum ada pagination atau klaim bahwa semua record di atas batas tersebut ditampilkan.
- Jadwal menampilkan semua status/tanggal yang tersimpan dalam batas daftar, termasuk tanggal lampau; heading memakai Tour Dates/Schedule agar tidak mengklaim semuanya acara mendatang. Status tidak diubah dan belum dipakai untuk mengaktifkan booking.
- Tanggal rilis album dan tanggal berita ditampilkan dari nilai DATE/bagian tanggal DATETIME yang tersimpan; konversi UTC→WIB diterapkan pada TIMESTAMP konser. Data impor TIMESTAMP perlu menggunakan timezone session yang sesuai dengan sumbernya.
- Detail artikel: slug hilang/tidak ditemukan menghasilkan 404; input array, UTF-8 invalid, atau melebihi panjang yang didukung ditolak. Isi artikel dan lirik dirender sebagai teks dengan baris tetap terbaca, bukan HTML mentah.
- Gambar album hanya memakai berkas raster lokal yang valid di `assets/`; URL tidak aman atau path tidak tersedia memakai ilustrasi fallback. Berita belum memiliki kolom gambar pada skema, sehingga menggunakan ilustrasi arsip yang jelas sebagai artwork proyek.
- Header/footer, menu mobile, gaya editorial, grid, dan breakpoint utama dipertahankan. Konten pendukung editorial/galeri tetap visual statis; galeri tidak lagi mengklaim tanggal konser hardcoded. Konsep paket VIP ditandai belum dapat dipesan.
- Form newsletter pada News belum diaktifkan; tampil disabled dengan keterangan tahap berikutnya. Halaman detail mengarah ke informasi newsletter. Tidak ada klaim pesan sudah disimpan/dikirim.

## Bukti pengujian

Perintah terverifikasi ada di [TESTING_3_2.md](TESTING_3_2.md). Runner fondasi menjalankan pemeriksaan section 3.2 melalui [tests/content.ps1](../tests/content.ps1) pada database terisolasi.

Hasil yang lulus:

1. Sintaks 13 file PHP serta `node --check script.js`.
2. Keadaan kosong News/Tour dan pembacaan album/lagu/berita/venue/konser dari fixture dummy; integritas join dan urutan tampil.
3. Pencarian isi lirik menghasilkan satu baris cocok; pencarian tidak cocok menghasilkan nol; input array dan UTF-8 invalid ditolak.
4. Judul/konten/lirik/venue dengan markup ditampilkan sebagai teks yang di-escape. Gambar `javascript:` tidak menjadi sumber gambar aktif.
5. Artikel/konser tidak ditemukan menghasilkan 404; ID tidak valid menghasilkan 400; slug yang menyerupai injeksi SQL tidak membuka artikel lain.
6. Perubahan judul berita pada database uji tampil pada Home dan halaman detail; waktu konser fixture tampil dalam WIB.
7. Lima alias `.html` menghasilkan 308 dan query slug dipertahankan; 26 target tautan/aset yang ditemukan dari delapan halaman dapat diakses dengan hasil yang diharapkan.
8. Kegagalan kredensial menghasilkan halaman dinamis 503 tanpa SQLSTATE, stack trace, atau kredensial; halaman About yang masih statis tetap dapat diakses.

Pemeriksaan browser menggunakan fixture terpisah:

- Music: pencarian berdasarkan frasa yang hanya ada dalam lirik; pencarian tanpa hasil; Read Lyrics membuka `details` lagu yang benar.
- Mobile 390×844: menu dapat dibuka dan ditutup dengan Escape, fokus kembali pada tombol; Tour, Music, dan detail artikel tidak menunjukkan overflow horizontal pada pemeriksaan DOM. Anchor mendapat scroll margin untuk header sticky.
- Desktop: tautan Read More membuka artikel dari slug, Home menampilkan berita/konser yang konsisten, dan tidak ditemukan gambar rusak pada halaman yang diperiksa.
- Bukti gambar: [Tour mobile](../output/screenshots/section-3-2-tour-mobile.jpg) dan [detail berita desktop](../output/screenshots/section-3-2-news-detail.jpg). Screenshot memakai data dummy keamanan seperti string `<script>` yang tampil sebagai teks; bukan konten band terverifikasi.

Runner terbaru menulis `content-evidence.json` pada folder run `.runtime/foundation-<id>/` yang dicetak terminal. Hanya artefak lokal; jangan unggah seluruh `.runtime/`. Laporan 3.1 tetap catatan historis kondisi sebelum halaman diintegrasikan.

Run akhir pekerjaan ini: `.runtime/foundation-0711a80c861a42638c76baf85aaeba93/content-evidence.json`. Bukti tersimpan diperiksa kembali: satu hasil pencarian lirik, nol hasil untuk kata yang tidak cocok, konversi WIB sesuai fixture, dan 26 resource terverifikasi. Log mengonfirmasi Shutdown complete. Proses preview browser dan file kredensial sementaranya juga sudah dibersihkan.

Smoke test terakhir memakai database tim yang masih kosong: `/`, `/music.php`, `/news.php`, dan `/tour.php` menghasilkan HTTP 200 dengan pesan kosong yang benar. Hanya pembacaan yang dilakukan; tidak ada seed atau perubahan data pada database tim.

## Batas dan langkah berikutnya

- Database tim pada awal pekerjaan ini berisi nol album, lagu, berita, dan konser. Membuka aplikasi dengan database tersebut akan menampilkan keadaan kosong yang benar sampai tim menambahkan konten. Tidak ada data dummy dimasukkan ke database tim.
- Shared Playlist adalah daftar lagu/lirik; belum ada pemutaran audio per lagu, akun, atau kurasi tersimpan. Embed YouTube lama tetap disertai link fallback; provider menampilkan video unavailable saat pemeriksaan browser, sehingga playback embed tidak dinyatakan lulus.
- Booking tiket/merch, autentikasi/role, serta kontak/newsletter tetap di tahap berikutnya. Pemilihan konser belum menulis order atau mengarah ke checkout hardcoded.
- Apache/hosting produksi belum diuji. Jalankan sesuai panduan PHP/router yang telah diverifikasi; periksa ulang proteksi file privat dan pengalihan bila beralih server.
- Tidak ada commit, push, deployment, atau perubahan sheet tim yang dilakukan oleh pekerjaan ini.

# Checklist eksekusi WBS 3.1–3.5

> Pembaruan 14 November 2026: tampilan publik kini HTML + JavaScript dan API PHP terpisah. Rujuk [HTML_PHP_SEPARATION.md](HTML_PHP_SEPARATION.md); catatan render PHP terdahulu adalah riwayat, bukan arsitektur aktif. Hosting backend HTTPS belum ditentukan.

Detail cakupan, dependensi, dan verifikasi setiap ID berada di [plan.md](plan.md). Ini satu-satunya checklist progres eksekusi rencana section 3; [BACKEND_TASKS.md](BACKEND_TASKS.md) tetap sebagai peta pekerjaan backend keseluruhan. Semua item awalnya belum diverifikasi, bukan pernyataan status resmi sheet tim. Centang hanya setelah kriteria tugas dan verifikasinya terpenuhi; catat hambatan pada tabel di bawah.

## 3.1 — Fondasi

- [x] T31-A — Verifikasi lingkungan dan database.
- [x] T31-B — Koneksi bersama dan satu alur baca PHP.
- [x] Checkpoint fondasi — Setup dapat diulang, koneksi dipakai bersama, konfigurasi terlindungi pada server development yang diuji.

Bukti dan batas lingkungan: [REPORT_3_1.md](REPORT_3_1.md). Cara menjalankan serta kontrak integrasi: [FOUNDATION_SETUP.md](FOUNDATION_SETUP.md).

## 3.2 — Konten dinamis

- [x] T32-A — Album dan lagu.
- [x] T32-B — Berita dan detail.
- [x] T32-C — Jadwal konser.
- [x] T32-D — Ringkasan Home dan tautan antarhalaman.
- [x] T32-E — Playlist daftar lagu bersama sesuai keputusan Abyan.
- [x] Checkpoint konten — Halaman dinamis, aset, encoding, dan navigasi telah diuji.

Bukti, keputusan playlist/WIB, serta batas: [REPORT_3_2.md](REPORT_3_2.md). Langkah uji: [TESTING_3_2.md](TESTING_3_2.md).

## 3.3 — Katalog dan pemesanan

- [ ] T33-A — Katalog merch dari database.
- [ ] T33-B — Kontrak booking dan keputusan bisnis.
- [ ] T33-C — Booking tiket yang konsisten; bergantung identitas pengguna terpercaya.
- [ ] T33-D — Order merch atau keputusan cakupan eksplisit dari tim.
- [ ] Checkpoint transaksi — Otorisasi, total, stok/kuota, rollback, dan submit ulang telah diuji.

## 3.4 — Interaksi pengguna

- [ ] T34-A — Definisi fitur dan pemetaan data.
- [ ] T34-B — Satu alur subscription lengkap.
- [ ] T34-C — Kontak, fans, dan message feed sesuai kontrak; pecah per form saat ditugaskan jika perlu.

## 3.5 — Verifikasi dan revisi

- [ ] T35-A — Uji integrasi dan perbaikan terarah.
- [ ] T35-B — Penutupan section 3 dan serah terima.
- [ ] Checkpoint akhir — Semua deliverable disepakati terpenuhi, hasil uji tercatat, hambatan tidak disamarkan.

## Catatan penugasan dan hambatan

Isi pemilik saat ditugaskan; jangan mengarang pembagian Abyan/Shaddam. Untuk status sebagian/menunggu input, tuliskan ID, bagian yang sudah terbukti, dan kebutuhan berikutnya. Tidak perlu mengubah sheet tim untuk memperbarui checklist ini.

| ID tugas | Pemilik/agent | Status dan bukti | Hambatan/input berikutnya |
|---|---|---|---|
| T31-A | Agent atas instruksi Abyan | Selesai lokal, 10 November 2026; 15 tabel/11 FK cocok dengan skrip | Status skema final secara organisasi tetap dikonfirmasi jika akan diubah |
| T31-B | Agent atas instruksi Abyan | Selesai; GET album lulus pada database tim dan data dummy terisolasi | Integrasi UI lanjutan pada 3.2; Apache belum diuji |
| T32-A–E | Agent atas instruksi Abyan | Selesai lokal; query/UI konten, Shared Playlist, WIB, error, dan redirect diuji | Konten tim masih kosong; booking, email, dan auth tahap selanjutnya; Apache belum diuji |

# Pintu masuk aturan agent

Instruksi ini berlaku untuk seluruh repositori. Semua dokumen kerja agent dikumpulkan di folder `agent/`.

**Sebelum mengubah file apa pun, wajib baca dan ikuti [agent/RULES.md](agent/RULES.md).** Aturan dalam dokumen tersebut berlaku untuk seluruh proyek, termasuk UI, JavaScript, PHP native, MySQL, keamanan, dan verifikasi. File root ini dipertahankan agar agent yang mencari `AGENTS.md` tetap menemukan panduannya.

## Urutan baca

1. [Aturan lengkap dan konfigurasi proyek](agent/RULES.md).
2. [Konteks tim, jadwal, dan kebutuhan input](agent/PROJECT_CONTEXT.md).
3. [Peta pekerjaan backend](agent/BACKEND_TASKS.md).
4. [Rencana WBS 3.1–3.5](agent/plan.md) dan [checklist eksekusi](agent/todo.md) jika tugas terkait section 3.
5. [Referensi desain proyek yang tersedia](output/DESIGN.md) serta file sumber aktual yang terkait dengan tugas.
6. [Setup dan kontrak fondasi PHP/MySQL](agent/FOUNDATION_SETUP.md), serta [laporan hasil WBS 3.1](agent/REPORT_3_1.md) untuk kondisi backend yang telah diverifikasi.
7. [Laporan WBS 3.2](agent/REPORT_3_2.md) dan [panduan pengujian konten dinamis](agent/TESTING_3_2.md) untuk routing PHP, playlist bersama, dan waktu WIB yang sekarang berlaku.
8. [Arsitektur HTML/API PHP, konfigurasi Pages, dan bukti terbaru](agent/HTML_PHP_SEPARATION.md); baca sebelum mengubah halaman publik.

Skema aplikasi tetap berada di [mcr_database_mysql.db](mcr_database_mysql.db). Kode, aset, dan skema adalah sumber proyek; jangan menduplikasinya ke folder agent. `output/` menyimpan artefak hasil seperti screenshot, PDF, dan ZIP.

Simpan panduan, rencana, dan catatan kerja agent berikutnya di `agent/`; perbarui dokumen yang relevan agar tidak muncul aturan atau checklist tandingan. Jangan mencatat kredensial atau data pribadi di dalamnya. Jangan mengarang informasi atau mengklaim pekerjaan/pengujian selesai tanpa bukti.

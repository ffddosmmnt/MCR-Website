# ERD MCR untuk Figma

Paket ini mengikuti struktur aktif MySQL: **19 tabel, 96 kolom, dan 14 foreign key**. Migrasi 002 menambahkan kontak, subscription, dan pesan fans tanpa FK baru. Verifikasi metadata 16 November berlaku untuk 16 tabel sebelum migrasi 002; tipe/nullability tabel baru dan keutuhan tabel lama diperiksa saat migrasi 17 November 2026. Generator membaca skema awal beserta migrasi 001 dan 002, tanpa membaca data pengguna.

## Berkas

| Berkas | Kegunaan |
|---|---|
| [ERD_FIGMA.svg](ERD_FIGMA.svg) | Diagram vektor siap diimpor ke kanvas Figma Design. Berkas ini juga merupakan sumber SVG dalam bentuk teks yang dapat diedit. |
| [ERD_MCR.mmd](ERD_MCR.mmd) | Script ERD Mermaid: seluruh tabel/kolom, PK/FK/UK, dan kardinalitas. |
| [STRUKTUR_TABEL.md](STRUKTUR_TABEL.md) | Kamus data: tipe, nullable, default, indeks, CHECK, relasi, dan aturan aplikasi yang terpisah dari constraint SQL. |

## Memasukkan diagram ke Figma Design

1. Buka file desain tujuan di Figma Design.
2. Tarik `ERD_FIGMA.svg` dari folder ini ke kanvas desain, bukan ke halaman file browser.
3. Pilih diagram dan ungroup sesuai kebutuhan untuk mengatur posisi atau warna elemen.
4. Simpan diagram dalam frame halaman perancangan database proyek.

SVG memakai elemen dasar rect/text/polyline dan tidak memakai marker/pattern. [Dokumentasi resmi Figma](https://help.figma.com/hc/en-us/articles/360040030374-Copy-assets-between-design-tools) mencatat bahwa marker/pattern SVG tidak disertakan saat impor. Konektor pada SVG adalah garis vektor; memindah tabel tidak otomatis merutekan ulang garis sebagai relasi database.

Figma tidak mengeksekusi SQL atau file `.mmd` sebagai kode database. Script Mermaid disediakan untuk konektor/plugin yang memang mendukung ERD Mermaid. Jika alat yang dipilih tidak mendukung `erDiagram`, gunakan SVG. Jangan mengubah nama/type kolom agar sesuai keterbatasan alat; tipe SQL lengkap ada pada komentar atribut Mermaid dan kamus data.

## Menggunakan konektor MCP Figma

Konektor Figma harus dipasang dan akun pengguna diotorisasi terlebih dahulu. Sebelum terhubung, tidak ada diagram yang dibuat di akun Figma. Setelah tool tersedia, gunakan kemampuan diagram yang didukung konektor dengan sumber ERD ini; hasilnya perlu diperiksa agar seluruh 19 tabel dan 14 relasi tetap ada. Kemampuan FigJam dan Figma Design dapat berbeda; jangan menganggap hasil FigJam otomatis menjadi file Figma Design.

## Membaca relasi

- `1 → 0..N`: tiap child wajib satu parent, tetapi parent boleh belum memiliki child.
- `0..1 ↔ 0..1`: cabang relasi opsional dengan FK nullable dan UNIQUE.
- `checkout_requests` memakai PK `(user_id, request_key)` dan tepat satu dari `ticket_order_id`/`merch_order_id` terisi. Kedua cabang digambar opsional karena masing-masing boleh NULL, tetapi CHECK menegakkan gabungannya.
- `band_members` dan `news_articles` tidak memiliki relasi FK dalam skema saat ini.
- Nilai default, AI, indeks gabungan, serta CHECK lengkap dijelaskan dalam struktur tabel; diagram bukan pengganti DDL SQL.

## Memperbarui rancangan setelah skema berubah

Jalankan dari root proyek:

```powershell
python agent/build_db_design.py
```

Generator lokal membaca `mcr_database_mysql.db`, `migrations/001_checkout_requests.sql`, dan `migrations/002_interactions.sql`, bukan salinan skema baru. Folder agent berisi alat kerja lokal dan tidak disertakan dalam publikasi Git sesuai konfigurasi repositori. Setelah menambah migrasi lain, perbarui daftar sumber dan layout generator. Periksa ulang terhadap metadata database sebelum menyatakan diagram terbaru. Pembuatan XML/SVG dan pencocokan metadata sudah diverifikasi; impor langsung ke akun Figma belum diuji karena konektor belum terhubung pada saat paket ini dibuat.

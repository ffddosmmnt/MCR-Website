# Perancangan dan struktur database MCR

Tanggal: 17 November 2026. Basis: skema sumber proyek beserta migrasi checkout dan interaksi.

## Sumber dan cakupan

- Database: `mcr_website`, MySQL, InnoDB, charset `utf8mb4`, collation `utf8mb4_unicode_ci`.
- 19 tabel: 15 tabel awal pada [mcr_database_mysql.db](../../mcr_database_mysql.db), migrasi [001_checkout_requests.sql](../../migrations/001_checkout_requests.sql), dan [002_interactions.sql](../../migrations/002_interactions.sql).
- 14 foreign key. Kunci/hash pada checkout_requests dan contact_messages memakai charset ascii/collation ascii_bin.
- Ini dokumentasi struktur, bukan dump data atau skrip pengganti sumber SQL. Tidak ada perubahan database pada pembuatan dokumen ini.

## Ringkasan tabel

| Tabel | Fungsi | Primary key |
|---|---|---|
| `band_members` | Profil anggota band dan instrumen. | `id` |
| `news_articles` | Berita dan artikel yang diakses melalui slug. | `id` |
| `users` | Akun pengguna, peran, dan hash password. | `id` |
| `albums` | Katalog album dan referensi cover. | `id` |
| `songs` | Lagu dan lirik yang menjadi bagian album. | `id` |
| `categories` | Kategori merchandise. | `id` |
| `products` | Produk merchandise dan harga dasar. | `id` |
| `product_variants` | Pilihan ukuran/warna produk dan stok masing-masing varian. | `id` |
| `merch_orders` | Header pesanan merchandise milik pengguna. | `id` |
| `merch_order_items` | Item varian dalam pesanan merchandise, termasuk alamat. | `id` |
| `venues` | Tempat konser beserta kota dan negara. | `id` |
| `concerts` | Acara konser, venue, waktu, dan status. | `id` |
| `ticket_types` | Jenis tiket, harga, dan kuota untuk satu konser. | `id` |
| `ticket_orders` | Header pesanan tiket milik pengguna. | `id` |
| `ticket_order_items` | Rincian jenis tiket dan jumlah pada pesanan. | `id` |
| `checkout_requests` | Kunci permintaan checkout untuk mencegah duplikasi order. | `user_id, request_key` |
| `contact_messages` | Pesan kontak privat; kunci unik mencegah duplikasi pengiriman. | `id` |
| `newsletter_subscriptions` | Pendaftaran email newsletter dengan persetujuan eksplisit. | `id` |
| `fan_messages` | Pesan fans pilihan admin yang dipublikasikan di News. | `id` |

## Struktur per tabel

`NULL: Ya` berarti kolom boleh kosong. Tanda `—` pada default berarti tidak ada klausa DEFAULT eksplisit pada sumber SQL. PK adalah primary key, FK foreign key, UK unique key per kolom. Default `NULL` berbeda dari nilai teks `'NULL'`.

### 1. `band_members`

Profil anggota band dan instrumen.

| Kolom | Tipe MySQL | NULL | Default sumber | Key | Keterangan |
|---|---|---|---|---|---|
| `id` | `INT UNSIGNED` | Tidak | — | PK, AI | Identitas record. |
| `name` | `VARCHAR(100)` | Tidak | — | — | Nama. |
| `instrument` | `VARCHAR(100)` | Ya | `NULL` | — | Instrumen/peran anggota band. |
| `cover_img` | `TEXT` | Ya | `NULL` | — | Referensi gambar anggota band. |

Indeks yang dideklarasikan:

- `PRIMARY`: PRIMARY KEY (`id`).

### 2. `news_articles`

Berita dan artikel yang diakses melalui slug.

| Kolom | Tipe MySQL | NULL | Default sumber | Key | Keterangan |
|---|---|---|---|---|---|
| `id` | `INT UNSIGNED` | Tidak | — | PK, AI | Identitas record. |
| `title` | `VARCHAR(200)` | Tidak | — | — | Judul. |
| `slug` | `VARCHAR(220)` | Tidak | — | UK | Identitas URL artikel; harus unik. |
| `content` | `TEXT` | Ya | `NULL` | — | Isi artikel. |
| `news_date` | `DATETIME` | Ya | `NULL` | — | Tanggal dan waktu berita. |

Indeks yang dideklarasikan:

- `PRIMARY`: PRIMARY KEY (`id`).
- `uq_news_articles_slug`: UNIQUE KEY (`slug`).

### 3. `users`

Akun pengguna, peran, dan hash password.

| Kolom | Tipe MySQL | NULL | Default sumber | Key | Keterangan |
|---|---|---|---|---|---|
| `id` | `INT UNSIGNED` | Tidak | — | PK, AI | Identitas record. |
| `name` | `VARCHAR(100)` | Tidak | — | — | Nama. |
| `email` | `VARCHAR(150)` | Tidak | — | UK | Alamat email akun; harus unik. |
| `password_hash` | `VARCHAR(255)` | Tidak | — | — | Hash password, bukan password asli. |
| `role` | `VARCHAR(20)` | Tidak | `'user'` | — | Peran akun; VARCHAR, bukan ENUM. |
| `time_stamp` | `TIMESTAMP` | Tidak | `CURRENT_TIMESTAMP` | — | Waktu pencatatan sebagai TIMESTAMP. |
| `created_at` | `DATETIME` | Tidak | `CURRENT_TIMESTAMP` | — | Waktu pembuatan record. |

Indeks yang dideklarasikan:

- `PRIMARY`: PRIMARY KEY (`id`).
- `uq_users_email`: UNIQUE KEY (`email`).

### 4. `albums`

Katalog album dan referensi cover.

| Kolom | Tipe MySQL | NULL | Default sumber | Key | Keterangan |
|---|---|---|---|---|---|
| `id` | `INT UNSIGNED` | Tidak | — | PK, AI | Identitas record. |
| `title` | `VARCHAR(150)` | Tidak | — | — | Judul. |
| `release_date` | `DATE` | Ya | `NULL` | — | Tanggal rilis album. |
| `cover_image` | `TEXT` | Ya | `NULL` | — | Referensi cover album. |

Indeks yang dideklarasikan:

- `PRIMARY`: PRIMARY KEY (`id`).

### 5. `songs`

Lagu dan lirik yang menjadi bagian album.

| Kolom | Tipe MySQL | NULL | Default sumber | Key | Keterangan |
|---|---|---|---|---|---|
| `id` | `INT UNSIGNED` | Tidak | — | PK, AI | Identitas record. |
| `album_id` | `INT UNSIGNED` | Tidak | — | FK | Referensi ke `albums.id`. |
| `title` | `VARCHAR(150)` | Tidak | — | — | Judul. |
| `lyrics` | `TEXT` | Ya | `NULL` | — | Teks lirik lagu. |

Indeks yang dideklarasikan:

- `PRIMARY`: PRIMARY KEY (`id`).
- `idx_songs_album_id`: KEY (`album_id`).

### 6. `categories`

Kategori merchandise.

| Kolom | Tipe MySQL | NULL | Default sumber | Key | Keterangan |
|---|---|---|---|---|---|
| `id` | `INT UNSIGNED` | Tidak | — | PK, AI | Identitas record. |
| `name` | `VARCHAR(100)` | Tidak | — | — | Nama. |

Indeks yang dideklarasikan:

- `PRIMARY`: PRIMARY KEY (`id`).

### 7. `products`

Produk merchandise dan harga dasar.

| Kolom | Tipe MySQL | NULL | Default sumber | Key | Keterangan |
|---|---|---|---|---|---|
| `id` | `INT UNSIGNED` | Tidak | — | PK, AI | Identitas record. |
| `category_id` | `INT UNSIGNED` | Tidak | — | FK | Referensi ke `categories.id`. |
| `name` | `VARCHAR(150)` | Tidak | — | — | Nama. |
| `price` | `DECIMAL(12,2)` | Tidak | `0.00` | — | Harga dalam IDR; presisi dua desimal. |
| `details` | `TEXT` | Ya | `NULL` | — | Deskripsi produk. |

Indeks yang dideklarasikan:

- `PRIMARY`: PRIMARY KEY (`id`).
- `idx_products_category_id`: KEY (`category_id`).

CHECK:

- `chk_products_price`: ``price` >= 0`.

### 8. `product_variants`

Pilihan ukuran/warna produk dan stok masing-masing varian.

| Kolom | Tipe MySQL | NULL | Default sumber | Key | Keterangan |
|---|---|---|---|---|---|
| `id` | `INT UNSIGNED` | Tidak | — | PK, AI | Identitas record. |
| `product_id` | `INT UNSIGNED` | Tidak | — | FK | Referensi ke `products.id`. |
| `size` | `VARCHAR(30)` | Ya | `NULL` | — | Ukuran varian. |
| `color` | `VARCHAR(50)` | Ya | `NULL` | — | Warna varian. |
| `stock` | `INT` | Tidak | `0` | — | Stok tersedia untuk varian. |

Indeks yang dideklarasikan:

- `PRIMARY`: PRIMARY KEY (`id`).
- `idx_product_variants_product_id`: KEY (`product_id`).

CHECK:

- `chk_product_variants_stock`: ``stock` >= 0`.

### 9. `merch_orders`

Header pesanan merchandise milik pengguna.

| Kolom | Tipe MySQL | NULL | Default sumber | Key | Keterangan |
|---|---|---|---|---|---|
| `id` | `INT UNSIGNED` | Tidak | — | PK, AI | Identitas record. |
| `user_id` | `INT UNSIGNED` | Tidak | — | FK | Referensi ke `users.id`. |
| `total_amount` | `DECIMAL(12,2)` | Tidak | `0.00` | — | Total pesanan yang dihitung aplikasi dari subtotal item. |
| `status` | `VARCHAR(30)` | Tidak | `'pending'` | — | Status record; VARCHAR, bukan ENUM. |

Indeks yang dideklarasikan:

- `PRIMARY`: PRIMARY KEY (`id`).
- `idx_merch_orders_user_id`: KEY (`user_id`).

CHECK:

- `chk_merch_orders_total`: ``total_amount` >= 0`.

### 10. `merch_order_items`

Item varian dalam pesanan merchandise, termasuk alamat.

| Kolom | Tipe MySQL | NULL | Default sumber | Key | Keterangan |
|---|---|---|---|---|---|
| `id` | `INT UNSIGNED` | Tidak | — | PK, AI | Identitas record. |
| `order_id` | `INT UNSIGNED` | Tidak | — | FK | Referensi ke `merch_orders.id`. |
| `variant_id` | `INT UNSIGNED` | Tidak | — | FK | Referensi ke `product_variants.id`. |
| `quantity` | `INT` | Tidak | — | — | Jumlah unit; harus lebih dari nol. |
| `unit_price` | `DECIMAL(12,2)` | Tidak | `0.00` | — | Harga satuan yang dicatat saat item dibuat. |
| `subtotal` | `DECIMAL(12,2)` | Tidak | `0.00` | — | Harga satuan dikalikan jumlah, dihitung aplikasi. |
| `time_stamp` | `TIMESTAMP` | Tidak | `CURRENT_TIMESTAMP` | — | Waktu pencatatan sebagai TIMESTAMP. |
| `created_at` | `DATETIME` | Tidak | `CURRENT_TIMESTAMP` | — | Waktu pembuatan record. |
| `address` | `TEXT` | Ya | `NULL` | — | Alamat merchandise pada masing-masing item. |

Indeks yang dideklarasikan:

- `PRIMARY`: PRIMARY KEY (`id`).
- `idx_merch_order_items_order_id`: KEY (`order_id`).
- `idx_merch_order_items_variant_id`: KEY (`variant_id`).

CHECK:

- `chk_merch_order_items_quantity`: ``quantity` > 0`.
- `chk_merch_order_items_unit_price`: ``unit_price` >= 0`.
- `chk_merch_order_items_subtotal`: ``subtotal` >= 0`.

### 11. `venues`

Tempat konser beserta kota dan negara.

| Kolom | Tipe MySQL | NULL | Default sumber | Key | Keterangan |
|---|---|---|---|---|---|
| `id` | `INT UNSIGNED` | Tidak | — | PK, AI | Identitas record. |
| `name` | `VARCHAR(150)` | Tidak | — | — | Nama. |
| `city` | `VARCHAR(100)` | Tidak | — | — | Kota venue. |
| `country` | `VARCHAR(100)` | Tidak | — | — | Negara venue. |

Indeks yang dideklarasikan:

- `PRIMARY`: PRIMARY KEY (`id`).

### 12. `concerts`

Acara konser, venue, waktu, dan status.

| Kolom | Tipe MySQL | NULL | Default sumber | Key | Keterangan |
|---|---|---|---|---|---|
| `id` | `INT UNSIGNED` | Tidak | — | PK, AI | Identitas record. |
| `venue_id` | `INT UNSIGNED` | Tidak | — | FK | Referensi ke `venues.id`. |
| `concert_date` | `TIMESTAMP` | Ya | `NULL` | — | Waktu konser; aplikasi membaca UTC dan menampilkan WIB. |
| `status` | `VARCHAR(30)` | Tidak | `'scheduled'` | — | Status record; VARCHAR, bukan ENUM. |

Indeks yang dideklarasikan:

- `PRIMARY`: PRIMARY KEY (`id`).
- `idx_concerts_venue_id`: KEY (`venue_id`).

### 13. `ticket_types`

Jenis tiket, harga, dan kuota untuk satu konser.

| Kolom | Tipe MySQL | NULL | Default sumber | Key | Keterangan |
|---|---|---|---|---|---|
| `id` | `INT UNSIGNED` | Tidak | — | PK, AI | Identitas record. |
| `concert_id` | `INT UNSIGNED` | Tidak | — | FK | Referensi ke `concerts.id`. |
| `name` | `VARCHAR(100)` | Tidak | — | — | Nama. |
| `price` | `DECIMAL(12,2)` | Tidak | `0.00` | — | Harga dalam IDR; presisi dua desimal. |
| `quota` | `INT` | Tidak | `0` | — | Kuota tersedia untuk jenis tiket. |

Indeks yang dideklarasikan:

- `PRIMARY`: PRIMARY KEY (`id`).
- `idx_ticket_types_concert_id`: KEY (`concert_id`).

CHECK:

- `chk_ticket_types_price`: ``price` >= 0`.
- `chk_ticket_types_quota`: ``quota` >= 0`.

### 14. `ticket_orders`

Header pesanan tiket milik pengguna.

| Kolom | Tipe MySQL | NULL | Default sumber | Key | Keterangan |
|---|---|---|---|---|---|
| `id` | `INT UNSIGNED` | Tidak | — | PK, AI | Identitas record. |
| `user_id` | `INT UNSIGNED` | Tidak | — | FK | Referensi ke `users.id`. |
| `total_amount` | `DECIMAL(12,2)` | Tidak | `0.00` | — | Total pesanan yang dihitung aplikasi dari subtotal item. |
| `status` | `VARCHAR(30)` | Tidak | `'pending'` | — | Status record; VARCHAR, bukan ENUM. |

Indeks yang dideklarasikan:

- `PRIMARY`: PRIMARY KEY (`id`).
- `idx_ticket_orders_user_id`: KEY (`user_id`).

CHECK:

- `chk_ticket_orders_total`: ``total_amount` >= 0`.

### 15. `ticket_order_items`

Rincian jenis tiket dan jumlah pada pesanan.

| Kolom | Tipe MySQL | NULL | Default sumber | Key | Keterangan |
|---|---|---|---|---|---|
| `id` | `INT UNSIGNED` | Tidak | — | PK, AI | Identitas record. |
| `order_id` | `INT UNSIGNED` | Tidak | — | FK | Referensi ke `ticket_orders.id`. |
| `ticket_type_id` | `INT UNSIGNED` | Tidak | — | FK | Referensi ke `ticket_types.id`. |
| `quantity` | `INT` | Tidak | — | — | Jumlah unit; harus lebih dari nol. |
| `unit_price` | `DECIMAL(12,2)` | Tidak | `0.00` | — | Harga satuan yang dicatat saat item dibuat. |
| `subtotal` | `DECIMAL(12,2)` | Tidak | `0.00` | — | Harga satuan dikalikan jumlah, dihitung aplikasi. |
| `order_code` | `INT` | Ya | `NULL` | — | Kode tambahan pada item tiket; nullable dan tidak memiliki UNIQUE. |
| `time_stamp` | `TIMESTAMP` | Tidak | `CURRENT_TIMESTAMP` | — | Waktu pencatatan sebagai TIMESTAMP. |
| `created_at` | `DATETIME` | Tidak | `CURRENT_TIMESTAMP` | — | Waktu pembuatan record. |

Indeks yang dideklarasikan:

- `PRIMARY`: PRIMARY KEY (`id`).
- `idx_ticket_order_items_order_id`: KEY (`order_id`).
- `idx_ticket_order_items_ticket_type_id`: KEY (`ticket_type_id`).

CHECK:

- `chk_ticket_order_items_quantity`: ``quantity` > 0`.
- `chk_ticket_order_items_unit_price`: ``unit_price` >= 0`.
- `chk_ticket_order_items_subtotal`: ``subtotal` >= 0`.

### 16. `checkout_requests`

Kunci permintaan checkout untuk mencegah duplikasi order.

| Kolom | Tipe MySQL | NULL | Default sumber | Key | Keterangan |
|---|---|---|---|---|---|
| `user_id` | `INT UNSIGNED` | Tidak | — | PK, FK | Referensi ke `users.id`. |
| `request_key` | `CHAR(64)` | Tidak | — | PK | Kunci permintaan; bersama user_id membentuk primary key. |
| `payload_hash` | `CHAR(64)` | Tidak | — | — | Hash SHA-256 pilihan checkout yang dinormalisasi aplikasi. |
| `ticket_order_id` | `INT UNSIGNED` | Ya | `NULL` | FK, UK | Referensi ke `ticket_orders.id`. |
| `merch_order_id` | `INT UNSIGNED` | Ya | `NULL` | FK, UK | Referensi ke `merch_orders.id`. |
| `created_at` | `TIMESTAMP` | Tidak | `CURRENT_TIMESTAMP` | — | Waktu pembuatan record. |

Indeks yang dideklarasikan:

- `PRIMARY`: PRIMARY KEY (`user_id, request_key`).
- `uq_checkout_ticket`: UNIQUE KEY (`ticket_order_id`).
- `uq_checkout_merch`: UNIQUE KEY (`merch_order_id`).

CHECK:

- `chk_checkout_one_order`: `(ticket_order_id IS NULL) <> (merch_order_id IS NULL)`.

### 17. `contact_messages`

Pesan kontak privat; kunci unik mencegah duplikasi pengiriman.

| Kolom | Tipe MySQL | NULL | Default sumber | Key | Keterangan |
|---|---|---|---|---|---|
| `id` | `INT UNSIGNED` | Tidak | — | PK, AI | Identitas record. |
| `name` | `VARCHAR(100)` | Tidak | — | — | Nama. |
| `email` | `VARCHAR(150)` | Tidak | — | — | Alamat kontak/pelanggan; unik hanya pada newsletter_subscriptions. |
| `subject` | `VARCHAR(200)` | Tidak | — | — | Subjek kontak privat. |
| `message` | `TEXT` | Tidak | — | — | Isi pesan teks biasa. |
| `request_key` | `CHAR(64)` | Tidak | — | UK | Kunci acak 64 hex, unik untuk satu pengiriman kontak. |
| `payload_hash` | `CHAR(64)` | Tidak | — | — | SHA-256 nama/email/subjek/pesan kontak yang dinormalisasi. |
| `created_at` | `TIMESTAMP` | Tidak | `CURRENT_TIMESTAMP` | — | Waktu pembuatan record. |

Indeks yang dideklarasikan:

- `PRIMARY`: PRIMARY KEY (`id`).
- `uq_contact_request`: UNIQUE KEY (`request_key`).

### 18. `newsletter_subscriptions`

Pendaftaran email newsletter dengan persetujuan eksplisit.

| Kolom | Tipe MySQL | NULL | Default sumber | Key | Keterangan |
|---|---|---|---|---|---|
| `id` | `INT UNSIGNED` | Tidak | — | PK, AI | Identitas record. |
| `email` | `VARCHAR(150)` | Tidak | — | UK | Alamat kontak/pelanggan; unik hanya pada newsletter_subscriptions. |
| `created_at` | `TIMESTAMP` | Tidak | `CURRENT_TIMESTAMP` | — | Waktu pembuatan record. |

Indeks yang dideklarasikan:

- `PRIMARY`: PRIMARY KEY (`id`).
- `uq_subscription_email`: UNIQUE KEY (`email`).

### 19. `fan_messages`

Pesan fans pilihan admin yang dipublikasikan di News.

| Kolom | Tipe MySQL | NULL | Default sumber | Key | Keterangan |
|---|---|---|---|---|---|
| `id` | `INT UNSIGNED` | Tidak | — | PK, AI | Identitas record. |
| `display_name` | `VARCHAR(100)` | Tidak | — | — | Nama publik yang dipilih admin. |
| `message` | `TEXT` | Tidak | — | — | Isi pesan teks biasa. |
| `created_at` | `TIMESTAMP` | Tidak | `CURRENT_TIMESTAMP` | — | Waktu pembuatan record. |

Indeks yang dideklarasikan:

- `PRIMARY`: PRIMARY KEY (`id`).

## Relasi dan kardinalitas

| Parent | Child dan FK | Kardinalitas fisik | ON UPDATE | ON DELETE |
|---|---|---|---|---|
| `albums` | `songs.album_id` | Satu parent → 0..N child; child wajib satu parent | CASCADE | RESTRICT |
| `categories` | `products.category_id` | Satu parent → 0..N child; child wajib satu parent | CASCADE | RESTRICT |
| `products` | `product_variants.product_id` | Satu parent → 0..N child; child wajib satu parent | CASCADE | RESTRICT |
| `users` | `merch_orders.user_id` | Satu parent → 0..N child; child wajib satu parent | CASCADE | RESTRICT |
| `merch_orders` | `merch_order_items.order_id` | Satu parent → 0..N child; child wajib satu parent | CASCADE | RESTRICT |
| `product_variants` | `merch_order_items.variant_id` | Satu parent → 0..N child; child wajib satu parent | CASCADE | RESTRICT |
| `venues` | `concerts.venue_id` | Satu parent → 0..N child; child wajib satu parent | CASCADE | RESTRICT |
| `concerts` | `ticket_types.concert_id` | Satu parent → 0..N child; child wajib satu parent | CASCADE | RESTRICT |
| `users` | `ticket_orders.user_id` | Satu parent → 0..N child; child wajib satu parent | CASCADE | RESTRICT |
| `ticket_orders` | `ticket_order_items.order_id` | Satu parent → 0..N child; child wajib satu parent | CASCADE | RESTRICT |
| `ticket_types` | `ticket_order_items.ticket_type_id` | Satu parent → 0..N child; child wajib satu parent | CASCADE | RESTRICT |
| `users` | `checkout_requests.user_id` | Satu parent → 0..N child; child wajib satu parent | CASCADE | RESTRICT |
| `ticket_orders` | `checkout_requests.ticket_order_id` | Parent 0..1 ↔ child 0..1 | RESTRICT | RESTRICT |
| `merch_orders` | `checkout_requests.merch_order_id` | Parent 0..1 ↔ child 0..1 | RESTRICT | RESTRICT |

## Catatan integritas yang penting

- `band_members` dan `news_articles` berdiri sendiri. Tidak ada FK penulis berita, anggota–album, atau pengguna–playlist; diagram tidak menambahkan relasi fiktif.
- Semua FK menggunakan ON DELETE RESTRICT. Tidak ada cascade delete.
- `checkout_requests` mempunyai PK komposit `(user_id, request_key)`. Satu user dapat memiliki banyak request_key; satu request_key dapat dipakai user berbeda.
- `ticket_order_id` dan `merch_order_id` nullable serta masing-masing UNIQUE. CHECK memastikan tepat satu kolom diisi; keduanya kosong atau keduanya terisi ditolak. Relasi tiap cabang opsional 0..1; gabungannya wajib tepat satu order.
- PK komposit menjamin kombinasi user/request unik. Kesesuaian pemilik order terhadap user_id request diperiksa aplikasi, bukan FK komposit pada tabel ini.
- Header order dapat memiliki nol item menurut skema fisik. Alur checkout aplikasi menyimpan minimal satu item dalam transaksi; kardinalitas ERD mengikuti constraint database.
- Total/subtotal, maksimum dua tiket per akun per konser, validasi status/tanggal konser, pengurangan stok, dan pengecekan payload_hash adalah aturan aplikasi; tidak semuanya ditegakkan oleh CHECK/FK.
- Nominal uang DECIMAL(12,2), IDR. Status dan role adalah VARCHAR dengan default, bukan daftar ENUM tertutup.
- `order_code` bukan kode unik dan boleh NULL. Alamat merchandise berada pada item pesanan. Header order tidak memiliki kolom tanggal; jangan mengarang field tersebut.
- Tabel produk belum memiliki kolom gambar. Akun tidak memiliki kolom nomor telepon. Kontak privat, subscription dan pesan fans memakai tiga tabel terpisah tanpa FK akun; tidak ada tabel pembayaran atau playlist pribadi.
- `TIMESTAMP` dan `DATETIME` bukan tipe yang identik. Waktu konser dikonversi UTC–WIB oleh aplikasi. `time_stamp` dan `created_at` tetap dua kolom berbeda sesuai sumber.
- Indeks FK yang tidak dideklarasikan eksplisit dapat dibuat otomatis oleh InnoDB. Daftar di atas mencatat indeks pada sumber; misalnya indeks PRIMARY(user_id, request_key) mencakup kebutuhan indeks FK user_id.

-- Konten awal proyek kuliah. Bukan dump database pengguna.
-- Album/lagu: metadata katalog. Lirik Welcome to the Black Parade dan Na Na Na diberikan pengguna; lirik lain belum disertakan.
-- Venue, konser 2027, tiket, merchandise, harga/stok dan pesan fans: contoh fiktif.
-- Baca database/README.md untuk sumber dan cara mengganti materi.
-- Pilih database aplikasi dan impor skema + migrasi 001/002/003 terlebih dahulu.
-- Jalankan php database/import.php agar kegagalan dibatalkan seluruhnya.
-- Impor ulang tidak mereset stok. UPDATE terbatas pada gambar pengganti yang dikenal dan pengisian lirik kosong yang diberikan pengguna.
-- Tiga berita bersumber internet ditambahkan 18 November 2026; tanggal record adalah tanggal kurasi lokal.
SET NAMES utf8mb4;
SET time_zone = '+00:00';
START TRANSACTION;

INSERT INTO `albums` (`title`, `release_date`, `cover_image`)
SELECT 'I Brought You My Bullets, You Brought Me Your Love', '2002-07-23', 'assets/albums/bullets.jpg'
WHERE NOT EXISTS (SELECT 1 FROM `albums` WHERE `title` <=> 'I Brought You My Bullets, You Brought Me Your Love');

INSERT INTO `songs` (`album_id`, `title`, `lyrics`)
SELECT (SELECT id FROM `albums` WHERE `title` <=> 'I Brought You My Bullets, You Brought Me Your Love'), 'Romance', NULL
WHERE NOT EXISTS (SELECT 1 FROM `songs` WHERE `album_id` <=> (SELECT id FROM `albums` WHERE `title` <=> 'I Brought You My Bullets, You Brought Me Your Love') AND `title` <=> 'Romance');

INSERT INTO `songs` (`album_id`, `title`, `lyrics`)
SELECT (SELECT id FROM `albums` WHERE `title` <=> 'I Brought You My Bullets, You Brought Me Your Love'), 'Honey, This Mirror Isn''t Big Enough for the Two of Us', NULL
WHERE NOT EXISTS (SELECT 1 FROM `songs` WHERE `album_id` <=> (SELECT id FROM `albums` WHERE `title` <=> 'I Brought You My Bullets, You Brought Me Your Love') AND `title` <=> 'Honey, This Mirror Isn''t Big Enough for the Two of Us');

INSERT INTO `songs` (`album_id`, `title`, `lyrics`)
SELECT (SELECT id FROM `albums` WHERE `title` <=> 'I Brought You My Bullets, You Brought Me Your Love'), 'Vampires Will Never Hurt You', NULL
WHERE NOT EXISTS (SELECT 1 FROM `songs` WHERE `album_id` <=> (SELECT id FROM `albums` WHERE `title` <=> 'I Brought You My Bullets, You Brought Me Your Love') AND `title` <=> 'Vampires Will Never Hurt You');

INSERT INTO `songs` (`album_id`, `title`, `lyrics`)
SELECT (SELECT id FROM `albums` WHERE `title` <=> 'I Brought You My Bullets, You Brought Me Your Love'), 'Skylines and Turnstiles', NULL
WHERE NOT EXISTS (SELECT 1 FROM `songs` WHERE `album_id` <=> (SELECT id FROM `albums` WHERE `title` <=> 'I Brought You My Bullets, You Brought Me Your Love') AND `title` <=> 'Skylines and Turnstiles');

INSERT INTO `songs` (`album_id`, `title`, `lyrics`)
SELECT (SELECT id FROM `albums` WHERE `title` <=> 'I Brought You My Bullets, You Brought Me Your Love'), 'Demolition Lovers', NULL
WHERE NOT EXISTS (SELECT 1 FROM `songs` WHERE `album_id` <=> (SELECT id FROM `albums` WHERE `title` <=> 'I Brought You My Bullets, You Brought Me Your Love') AND `title` <=> 'Demolition Lovers');

INSERT INTO `albums` (`title`, `release_date`, `cover_image`)
SELECT 'Three Cheers for Sweet Revenge', '2004-06-08', 'assets/albums/three-cheers.jpg'
WHERE NOT EXISTS (SELECT 1 FROM `albums` WHERE `title` <=> 'Three Cheers for Sweet Revenge');

INSERT INTO `songs` (`album_id`, `title`, `lyrics`)
SELECT (SELECT id FROM `albums` WHERE `title` <=> 'Three Cheers for Sweet Revenge'), 'Helena', NULL
WHERE NOT EXISTS (SELECT 1 FROM `songs` WHERE `album_id` <=> (SELECT id FROM `albums` WHERE `title` <=> 'Three Cheers for Sweet Revenge') AND `title` <=> 'Helena');

INSERT INTO `songs` (`album_id`, `title`, `lyrics`)
SELECT (SELECT id FROM `albums` WHERE `title` <=> 'Three Cheers for Sweet Revenge'), 'I''m Not Okay (I Promise)', NULL
WHERE NOT EXISTS (SELECT 1 FROM `songs` WHERE `album_id` <=> (SELECT id FROM `albums` WHERE `title` <=> 'Three Cheers for Sweet Revenge') AND `title` <=> 'I''m Not Okay (I Promise)');

INSERT INTO `songs` (`album_id`, `title`, `lyrics`)
SELECT (SELECT id FROM `albums` WHERE `title` <=> 'Three Cheers for Sweet Revenge'), 'The Ghost of You', NULL
WHERE NOT EXISTS (SELECT 1 FROM `songs` WHERE `album_id` <=> (SELECT id FROM `albums` WHERE `title` <=> 'Three Cheers for Sweet Revenge') AND `title` <=> 'The Ghost of You');

INSERT INTO `songs` (`album_id`, `title`, `lyrics`)
SELECT (SELECT id FROM `albums` WHERE `title` <=> 'Three Cheers for Sweet Revenge'), 'Thank You for the Venom', NULL
WHERE NOT EXISTS (SELECT 1 FROM `songs` WHERE `album_id` <=> (SELECT id FROM `albums` WHERE `title` <=> 'Three Cheers for Sweet Revenge') AND `title` <=> 'Thank You for the Venom');

INSERT INTO `songs` (`album_id`, `title`, `lyrics`)
SELECT (SELECT id FROM `albums` WHERE `title` <=> 'Three Cheers for Sweet Revenge'), 'It''s Not a Fashion Statement It''s a Deathwish', NULL
WHERE NOT EXISTS (SELECT 1 FROM `songs` WHERE `album_id` <=> (SELECT id FROM `albums` WHERE `title` <=> 'Three Cheers for Sweet Revenge') AND `title` <=> 'It''s Not a Fashion Statement It''s a Deathwish');

INSERT INTO `albums` (`title`, `release_date`, `cover_image`)
SELECT 'The Black Parade', '2006-10-23', 'assets/albums/black-parade.jpg'
WHERE NOT EXISTS (SELECT 1 FROM `albums` WHERE `title` <=> 'The Black Parade');

INSERT INTO `songs` (`album_id`, `title`, `lyrics`)
SELECT (SELECT id FROM `albums` WHERE `title` <=> 'The Black Parade'), 'Welcome to the Black Parade', NULL
WHERE NOT EXISTS (SELECT 1 FROM `songs` WHERE `album_id` <=> (SELECT id FROM `albums` WHERE `title` <=> 'The Black Parade') AND `title` <=> 'Welcome to the Black Parade');

INSERT INTO `songs` (`album_id`, `title`, `lyrics`)
SELECT (SELECT id FROM `albums` WHERE `title` <=> 'The Black Parade'), 'I Don''t Love You', NULL
WHERE NOT EXISTS (SELECT 1 FROM `songs` WHERE `album_id` <=> (SELECT id FROM `albums` WHERE `title` <=> 'The Black Parade') AND `title` <=> 'I Don''t Love You');

INSERT INTO `songs` (`album_id`, `title`, `lyrics`)
SELECT (SELECT id FROM `albums` WHERE `title` <=> 'The Black Parade'), 'Cancer', NULL
WHERE NOT EXISTS (SELECT 1 FROM `songs` WHERE `album_id` <=> (SELECT id FROM `albums` WHERE `title` <=> 'The Black Parade') AND `title` <=> 'Cancer');

INSERT INTO `songs` (`album_id`, `title`, `lyrics`)
SELECT (SELECT id FROM `albums` WHERE `title` <=> 'The Black Parade'), 'Teenagers', NULL
WHERE NOT EXISTS (SELECT 1 FROM `songs` WHERE `album_id` <=> (SELECT id FROM `albums` WHERE `title` <=> 'The Black Parade') AND `title` <=> 'Teenagers');

INSERT INTO `songs` (`album_id`, `title`, `lyrics`)
SELECT (SELECT id FROM `albums` WHERE `title` <=> 'The Black Parade'), 'Famous Last Words', NULL
WHERE NOT EXISTS (SELECT 1 FROM `songs` WHERE `album_id` <=> (SELECT id FROM `albums` WHERE `title` <=> 'The Black Parade') AND `title` <=> 'Famous Last Words');

INSERT INTO `albums` (`title`, `release_date`, `cover_image`)
SELECT 'Danger Days: The True Lives of the Fabulous Killjoys', '2010-11-22', 'assets/albums/danger-days.jpg'
WHERE NOT EXISTS (SELECT 1 FROM `albums` WHERE `title` <=> 'Danger Days: The True Lives of the Fabulous Killjoys');

INSERT INTO `songs` (`album_id`, `title`, `lyrics`)
SELECT (SELECT id FROM `albums` WHERE `title` <=> 'Danger Days: The True Lives of the Fabulous Killjoys'), 'Na Na Na (Na Na Na Na Na Na Na Na Na)', NULL
WHERE NOT EXISTS (SELECT 1 FROM `songs` WHERE `album_id` <=> (SELECT id FROM `albums` WHERE `title` <=> 'Danger Days: The True Lives of the Fabulous Killjoys') AND `title` <=> 'Na Na Na (Na Na Na Na Na Na Na Na Na)');

INSERT INTO `songs` (`album_id`, `title`, `lyrics`)
SELECT (SELECT id FROM `albums` WHERE `title` <=> 'Danger Days: The True Lives of the Fabulous Killjoys'), 'SING', NULL
WHERE NOT EXISTS (SELECT 1 FROM `songs` WHERE `album_id` <=> (SELECT id FROM `albums` WHERE `title` <=> 'Danger Days: The True Lives of the Fabulous Killjoys') AND `title` <=> 'SING');

INSERT INTO `songs` (`album_id`, `title`, `lyrics`)
SELECT (SELECT id FROM `albums` WHERE `title` <=> 'Danger Days: The True Lives of the Fabulous Killjoys'), 'Summertime', NULL
WHERE NOT EXISTS (SELECT 1 FROM `songs` WHERE `album_id` <=> (SELECT id FROM `albums` WHERE `title` <=> 'Danger Days: The True Lives of the Fabulous Killjoys') AND `title` <=> 'Summertime');

INSERT INTO `songs` (`album_id`, `title`, `lyrics`)
SELECT (SELECT id FROM `albums` WHERE `title` <=> 'Danger Days: The True Lives of the Fabulous Killjoys'), 'Vampire Money', NULL
WHERE NOT EXISTS (SELECT 1 FROM `songs` WHERE `album_id` <=> (SELECT id FROM `albums` WHERE `title` <=> 'Danger Days: The True Lives of the Fabulous Killjoys') AND `title` <=> 'Vampire Money');

INSERT INTO `songs` (`album_id`, `title`, `lyrics`)
SELECT (SELECT id FROM `albums` WHERE `title` <=> 'Danger Days: The True Lives of the Fabulous Killjoys'), 'The Kids from Yesterday', NULL
WHERE NOT EXISTS (SELECT 1 FROM `songs` WHERE `album_id` <=> (SELECT id FROM `albums` WHERE `title` <=> 'Danger Days: The True Lives of the Fabulous Killjoys') AND `title` <=> 'The Kids from Yesterday');

INSERT INTO `band_members` (`name`, `instrument`, `cover_img`)
SELECT 'Gerard Way', 'Lead vocals', 'assets/members/gerard-way.jpg'
WHERE NOT EXISTS (SELECT 1 FROM `band_members` WHERE `name` <=> 'Gerard Way');

INSERT INTO `band_members` (`name`, `instrument`, `cover_img`)
SELECT 'Ray Toro', 'Lead guitar', 'assets/members/ray-toro.png'
WHERE NOT EXISTS (SELECT 1 FROM `band_members` WHERE `name` <=> 'Ray Toro');

INSERT INTO `band_members` (`name`, `instrument`, `cover_img`)
SELECT 'Frank Iero', 'Rhythm guitar', 'assets/members/frank-iero.jpg'
WHERE NOT EXISTS (SELECT 1 FROM `band_members` WHERE `name` <=> 'Frank Iero');

INSERT INTO `band_members` (`name`, `instrument`, `cover_img`)
SELECT 'Mikey Way', 'Bass guitar', 'assets/members/mikey-way.jpg'
WHERE NOT EXISTS (SELECT 1 FROM `band_members` WHERE `name` <=> 'Mikey Way');

INSERT INTO `news_articles` (`title`, `slug`, `content`, `news_date`)
SELECT 'Explore Four Chapters of My Chemical Romance', 'explore-four-chapters', 'Start with I Brought You My Bullets, You Brought Me Your Love (2002), then move through Three Cheers for Sweet Revenge (2004), The Black Parade (2006), and Danger Days: The True Lives of the Fabulous Killjoys (2010). These four albums offer a route through the band''s studio catalogue.

Browse Music to explore selected tracks grouped by album. This reading guide was written for this website; the album metadata follows the Apple Music catalogue linked in the project documentation.', '2026-11-18 10:00:00'
WHERE NOT EXISTS (SELECT 1 FROM `news_articles` WHERE `slug` <=> 'explore-four-chapters');

INSERT INTO `news_articles` (`title`, `slug`, `content`, `news_date`)
SELECT 'Inside the Shared Playlist', 'inside-the-shared-playlist', 'The Shared Playlist brings selected songs into one place. Each entry keeps its album connection so you can move between the early recordings, Three Cheers for Sweet Revenge, The Black Parade, and Danger Days.

Use the search field on Music to find a song by title or album. Open an entry for its available details. Lyrics can be added by the administrator when approved text is available.', '2026-11-17 10:00:00'
WHERE NOT EXISTS (SELECT 1 FROM `news_articles` WHERE `slug` <=> 'inside-the-shared-playlist');

INSERT INTO `news_articles` (`title`, `slug`, `content`, `news_date`)
SELECT 'The Black Parade: An Album to Revisit', 'the-black-parade-an-album-to-revisit', 'The Black Parade was released in 2006. The catalogue on this website includes Welcome to the Black Parade, I Don''t Love You, Cancer, Teenagers, and Famous Last Words from the album.

Visit Music to find these tracks in the Shared Playlist, or follow the video link provided there. This is an editorial listening note for this website, rather than an announcement from the band.', '2026-11-16 10:00:00'
WHERE NOT EXISTS (SELECT 1 FROM `news_articles` WHERE `slug` <=> 'the-black-parade-an-album-to-revisit');

INSERT INTO `news_articles` (`title`, `slug`, `content`, `news_date`)
SELECT 'From Three Cheers to Your Playlist', 'from-three-cheers-to-your-playlist', 'Three Cheers for Sweet Revenge was released in 2004. Helena, I''m Not Okay (I Promise), and The Ghost of You are among the tracks associated with the record.

The Music catalogue links selected titles back to their album. Use the album names in search to explore one release at a time and discover which titles you want to revisit next.', '2026-11-15 10:00:00'
WHERE NOT EXISTS (SELECT 1 FROM `news_articles` WHERE `slug` <=> 'from-three-cheers-to-your-playlist');

INSERT INTO `news_articles` (`title`, `slug`, `content`, `news_date`)
SELECT 'Your Guide to Tour Dates and Tickets', 'your-guide-to-tour-dates-and-tickets', 'The Tour page brings published venue details, event status, and concert times together. All times are displayed in WIB so you can review each date consistently.

Open Show details to inspect the concert, then review ticket options. Sign in to your Fan Account before placing an order. Ticket quantities are limited to two per account for each concert; availability and totals are checked by the server.', '2026-11-14 10:00:00'
WHERE NOT EXISTS (SELECT 1 FROM `news_articles` WHERE `slug` <=> 'your-guide-to-tour-dates-and-tickets');

INSERT INTO `news_articles` (`title`, `slug`, `content`, `news_date`)
SELECT 'Find Your Next Wardrobe Staple', 'find-your-next-wardrobe-staple', 'Explore tees, outerwear, accessories, and printed pieces in Store. Each product lists its available sizes or colours and the stock attached to that exact variant.

Choose a variant before adding an item to your bag. You can review the total and delivery address before placing an order. Keeping an item in the bag does not reserve its stock.', '2026-11-13 10:00:00'
WHERE NOT EXISTS (SELECT 1 FROM `news_articles` WHERE `slug` <=> 'find-your-next-wardrobe-staple');

INSERT INTO `categories` (`name`)
SELECT 'Tees'
WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE `name` <=> 'Tees');

INSERT INTO `products` (`category_id`, `name`, `price`, `details`)
SELECT (SELECT id FROM `categories` WHERE `name` <=> 'Tees'), 'Parade Emblem Tee', '185000.00', 'Black cotton tee with a front emblem. Regular fit.'
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Tees') AND `name` <=> 'Parade Emblem Tee');

INSERT INTO `product_variants` (`product_id`, `size`, `color`, `stock`)
SELECT (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Tees') AND `name` <=> 'Parade Emblem Tee'), 'S', 'Black', 12
WHERE NOT EXISTS (SELECT 1 FROM `product_variants` WHERE `product_id` <=> (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Tees') AND `name` <=> 'Parade Emblem Tee') AND `size` <=> 'S' AND `color` <=> 'Black');

INSERT INTO `product_variants` (`product_id`, `size`, `color`, `stock`)
SELECT (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Tees') AND `name` <=> 'Parade Emblem Tee'), 'M', 'Black', 20
WHERE NOT EXISTS (SELECT 1 FROM `product_variants` WHERE `product_id` <=> (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Tees') AND `name` <=> 'Parade Emblem Tee') AND `size` <=> 'M' AND `color` <=> 'Black');

INSERT INTO `product_variants` (`product_id`, `size`, `color`, `stock`)
SELECT (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Tees') AND `name` <=> 'Parade Emblem Tee'), 'L', 'Black', 18
WHERE NOT EXISTS (SELECT 1 FROM `product_variants` WHERE `product_id` <=> (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Tees') AND `name` <=> 'Parade Emblem Tee') AND `size` <=> 'L' AND `color` <=> 'Black');

INSERT INTO `product_variants` (`product_id`, `size`, `color`, `stock`)
SELECT (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Tees') AND `name` <=> 'Parade Emblem Tee'), 'XL', 'Black', 10
WHERE NOT EXISTS (SELECT 1 FROM `product_variants` WHERE `product_id` <=> (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Tees') AND `name` <=> 'Parade Emblem Tee') AND `size` <=> 'XL' AND `color` <=> 'Black');

INSERT INTO `categories` (`name`)
SELECT 'Tees'
WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE `name` <=> 'Tees');

INSERT INTO `products` (`category_id`, `name`, `price`, `details`)
SELECT (SELECT id FROM `categories` WHERE `name` <=> 'Tees'), 'Revenge Lettering Tee', '195000.00', 'Short-sleeve cotton tee with contrast lettering.'
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Tees') AND `name` <=> 'Revenge Lettering Tee');

INSERT INTO `product_variants` (`product_id`, `size`, `color`, `stock`)
SELECT (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Tees') AND `name` <=> 'Revenge Lettering Tee'), 'S', 'White', 10
WHERE NOT EXISTS (SELECT 1 FROM `product_variants` WHERE `product_id` <=> (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Tees') AND `name` <=> 'Revenge Lettering Tee') AND `size` <=> 'S' AND `color` <=> 'White');

INSERT INTO `product_variants` (`product_id`, `size`, `color`, `stock`)
SELECT (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Tees') AND `name` <=> 'Revenge Lettering Tee'), 'M', 'White', 16
WHERE NOT EXISTS (SELECT 1 FROM `product_variants` WHERE `product_id` <=> (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Tees') AND `name` <=> 'Revenge Lettering Tee') AND `size` <=> 'M' AND `color` <=> 'White');

INSERT INTO `product_variants` (`product_id`, `size`, `color`, `stock`)
SELECT (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Tees') AND `name` <=> 'Revenge Lettering Tee'), 'L', 'White', 14
WHERE NOT EXISTS (SELECT 1 FROM `product_variants` WHERE `product_id` <=> (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Tees') AND `name` <=> 'Revenge Lettering Tee') AND `size` <=> 'L' AND `color` <=> 'White');

INSERT INTO `categories` (`name`)
SELECT 'Outerwear'
WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE `name` <=> 'Outerwear');

INSERT INTO `products` (`category_id`, `name`, `price`, `details`)
SELECT (SELECT id FROM `categories` WHERE `name` <=> 'Outerwear'), 'Marching Zip Hoodie', '425000.00', 'Zip-front hoodie with a drawstring hood and front pockets.'
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Outerwear') AND `name` <=> 'Marching Zip Hoodie');

INSERT INTO `product_variants` (`product_id`, `size`, `color`, `stock`)
SELECT (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Outerwear') AND `name` <=> 'Marching Zip Hoodie'), 'M', 'Black', 10
WHERE NOT EXISTS (SELECT 1 FROM `product_variants` WHERE `product_id` <=> (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Outerwear') AND `name` <=> 'Marching Zip Hoodie') AND `size` <=> 'M' AND `color` <=> 'Black');

INSERT INTO `product_variants` (`product_id`, `size`, `color`, `stock`)
SELECT (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Outerwear') AND `name` <=> 'Marching Zip Hoodie'), 'L', 'Black', 12
WHERE NOT EXISTS (SELECT 1 FROM `product_variants` WHERE `product_id` <=> (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Outerwear') AND `name` <=> 'Marching Zip Hoodie') AND `size` <=> 'L' AND `color` <=> 'Black');

INSERT INTO `product_variants` (`product_id`, `size`, `color`, `stock`)
SELECT (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Outerwear') AND `name` <=> 'Marching Zip Hoodie'), 'XL', 'Black', 8
WHERE NOT EXISTS (SELECT 1 FROM `product_variants` WHERE `product_id` <=> (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Outerwear') AND `name` <=> 'Marching Zip Hoodie') AND `size` <=> 'XL' AND `color` <=> 'Black');

INSERT INTO `categories` (`name`)
SELECT 'Outerwear'
WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE `name` <=> 'Outerwear');

INSERT INTO `products` (`category_id`, `name`, `price`, `details`)
SELECT (SELECT id FROM `categories` WHERE `name` <=> 'Outerwear'), 'After Midnight Crewneck', '350000.00', 'Long-sleeve crewneck sweatshirt with ribbed cuffs.'
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Outerwear') AND `name` <=> 'After Midnight Crewneck');

INSERT INTO `product_variants` (`product_id`, `size`, `color`, `stock`)
SELECT (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Outerwear') AND `name` <=> 'After Midnight Crewneck'), 'M', 'Charcoal', 8
WHERE NOT EXISTS (SELECT 1 FROM `product_variants` WHERE `product_id` <=> (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Outerwear') AND `name` <=> 'After Midnight Crewneck') AND `size` <=> 'M' AND `color` <=> 'Charcoal');

INSERT INTO `product_variants` (`product_id`, `size`, `color`, `stock`)
SELECT (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Outerwear') AND `name` <=> 'After Midnight Crewneck'), 'L', 'Charcoal', 10
WHERE NOT EXISTS (SELECT 1 FROM `product_variants` WHERE `product_id` <=> (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Outerwear') AND `name` <=> 'After Midnight Crewneck') AND `size` <=> 'L' AND `color` <=> 'Charcoal');

INSERT INTO `product_variants` (`product_id`, `size`, `color`, `stock`)
SELECT (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Outerwear') AND `name` <=> 'After Midnight Crewneck'), 'XL', 'Charcoal', 0
WHERE NOT EXISTS (SELECT 1 FROM `product_variants` WHERE `product_id` <=> (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Outerwear') AND `name` <=> 'After Midnight Crewneck') AND `size` <=> 'XL' AND `color` <=> 'Charcoal');

INSERT INTO `categories` (`name`)
SELECT 'Accessories'
WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE `name` <=> 'Accessories');

INSERT INTO `products` (`category_id`, `name`, `price`, `details`)
SELECT (SELECT id FROM `categories` WHERE `name` <=> 'Accessories'), 'Parade Canvas Tote', '125000.00', 'Canvas carry bag for everyday essentials.'
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Accessories') AND `name` <=> 'Parade Canvas Tote');

INSERT INTO `product_variants` (`product_id`, `size`, `color`, `stock`)
SELECT (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Accessories') AND `name` <=> 'Parade Canvas Tote'), 'One size', 'Natural', 25
WHERE NOT EXISTS (SELECT 1 FROM `product_variants` WHERE `product_id` <=> (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Accessories') AND `name` <=> 'Parade Canvas Tote') AND `size` <=> 'One size' AND `color` <=> 'Natural');

INSERT INTO `product_variants` (`product_id`, `size`, `color`, `stock`)
SELECT (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Accessories') AND `name` <=> 'Parade Canvas Tote'), 'One size', 'Black', 20
WHERE NOT EXISTS (SELECT 1 FROM `product_variants` WHERE `product_id` <=> (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Accessories') AND `name` <=> 'Parade Canvas Tote') AND `size` <=> 'One size' AND `color` <=> 'Black');

INSERT INTO `categories` (`name`)
SELECT 'Accessories'
WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE `name` <=> 'Accessories');

INSERT INTO `products` (`category_id`, `name`, `price`, `details`)
SELECT (SELECT id FROM `categories` WHERE `name` <=> 'Accessories'), 'Enamel Pin Set', '85000.00', 'A set of three enamel pins with metal fastenings.'
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Accessories') AND `name` <=> 'Enamel Pin Set');

INSERT INTO `product_variants` (`product_id`, `size`, `color`, `stock`)
SELECT (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Accessories') AND `name` <=> 'Enamel Pin Set'), 'One size', 'Silver / Black', 30
WHERE NOT EXISTS (SELECT 1 FROM `product_variants` WHERE `product_id` <=> (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Accessories') AND `name` <=> 'Enamel Pin Set') AND `size` <=> 'One size' AND `color` <=> 'Silver / Black');

INSERT INTO `categories` (`name`)
SELECT 'Prints'
WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE `name` <=> 'Prints');

INSERT INTO `products` (`category_id`, `name`, `price`, `details`)
SELECT (SELECT id FROM `categories` WHERE `name` <=> 'Prints'), 'Night Parade Art Print', '95000.00', 'A3 monochrome art print on heavyweight paper. Frame not included.'
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Prints') AND `name` <=> 'Night Parade Art Print');

INSERT INTO `product_variants` (`product_id`, `size`, `color`, `stock`)
SELECT (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Prints') AND `name` <=> 'Night Parade Art Print'), 'A3', 'Monochrome', 18
WHERE NOT EXISTS (SELECT 1 FROM `product_variants` WHERE `product_id` <=> (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Prints') AND `name` <=> 'Night Parade Art Print') AND `size` <=> 'A3' AND `color` <=> 'Monochrome');

INSERT INTO `categories` (`name`)
SELECT 'Prints'
WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE `name` <=> 'Prints');

INSERT INTO `products` (`category_id`, `name`, `price`, `details`)
SELECT (SELECT id FROM `categories` WHERE `name` <=> 'Prints'), 'Archive Postcard Set', '65000.00', 'Six printed postcards presented in a paper sleeve.'
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Prints') AND `name` <=> 'Archive Postcard Set');

INSERT INTO `product_variants` (`product_id`, `size`, `color`, `stock`)
SELECT (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Prints') AND `name` <=> 'Archive Postcard Set'), 'One size', 'Monochrome', 24
WHERE NOT EXISTS (SELECT 1 FROM `product_variants` WHERE `product_id` <=> (SELECT id FROM `products` WHERE `category_id` <=> (SELECT id FROM `categories` WHERE `name` <=> 'Prints') AND `name` <=> 'Archive Postcard Set') AND `size` <=> 'One size' AND `color` <=> 'Monochrome');

INSERT INTO `venues` (`name`, `city`, `country`)
SELECT 'Parade Hall', 'Jakarta', 'Indonesia'
WHERE NOT EXISTS (SELECT 1 FROM `venues` WHERE `name` <=> 'Parade Hall' AND `city` <=> 'Jakarta' AND `country` <=> 'Indonesia');

INSERT INTO `venues` (`name`, `city`, `country`)
SELECT 'Killjoy Assembly Hall', 'Bandung', 'Indonesia'
WHERE NOT EXISTS (SELECT 1 FROM `venues` WHERE `name` <=> 'Killjoy Assembly Hall' AND `city` <=> 'Bandung' AND `country` <=> 'Indonesia');

INSERT INTO `venues` (`name`, `city`, `country`)
SELECT 'Revenge Auditorium', 'Surabaya', 'Indonesia'
WHERE NOT EXISTS (SELECT 1 FROM `venues` WHERE `name` <=> 'Revenge Auditorium' AND `city` <=> 'Surabaya' AND `country` <=> 'Indonesia');

INSERT INTO `concerts` (`venue_id`, `concert_date`, `status`)
SELECT (SELECT id FROM `venues` WHERE `name` <=> 'Parade Hall' AND `city` <=> 'Jakarta' AND `country` <=> 'Indonesia'), '2027-05-15 12:30:00', 'scheduled'
WHERE NOT EXISTS (SELECT 1 FROM `concerts` WHERE `venue_id` <=> (SELECT id FROM `venues` WHERE `name` <=> 'Parade Hall' AND `city` <=> 'Jakarta' AND `country` <=> 'Indonesia') AND `concert_date` <=> '2027-05-15 12:30:00');

INSERT INTO `ticket_types` (`concert_id`, `name`, `price`, `quota`)
SELECT (SELECT id FROM `concerts` WHERE `venue_id` <=> (SELECT id FROM `venues` WHERE `name` <=> 'Parade Hall' AND `city` <=> 'Jakarta' AND `country` <=> 'Indonesia') AND `concert_date` <=> '2027-05-15 12:30:00'), 'General Admission', '750000.00', 120
WHERE NOT EXISTS (SELECT 1 FROM `ticket_types` WHERE `concert_id` <=> (SELECT id FROM `concerts` WHERE `venue_id` <=> (SELECT id FROM `venues` WHERE `name` <=> 'Parade Hall' AND `city` <=> 'Jakarta' AND `country` <=> 'Indonesia') AND `concert_date` <=> '2027-05-15 12:30:00') AND `name` <=> 'General Admission');

INSERT INTO `ticket_types` (`concert_id`, `name`, `price`, `quota`)
SELECT (SELECT id FROM `concerts` WHERE `venue_id` <=> (SELECT id FROM `venues` WHERE `name` <=> 'Parade Hall' AND `city` <=> 'Jakarta' AND `country` <=> 'Indonesia') AND `concert_date` <=> '2027-05-15 12:30:00'), 'Balcony', '1100000.00', 60
WHERE NOT EXISTS (SELECT 1 FROM `ticket_types` WHERE `concert_id` <=> (SELECT id FROM `concerts` WHERE `venue_id` <=> (SELECT id FROM `venues` WHERE `name` <=> 'Parade Hall' AND `city` <=> 'Jakarta' AND `country` <=> 'Indonesia') AND `concert_date` <=> '2027-05-15 12:30:00') AND `name` <=> 'Balcony');

INSERT INTO `concerts` (`venue_id`, `concert_date`, `status`)
SELECT (SELECT id FROM `venues` WHERE `name` <=> 'Killjoy Assembly Hall' AND `city` <=> 'Bandung' AND `country` <=> 'Indonesia'), '2027-05-22 12:00:00', 'scheduled'
WHERE NOT EXISTS (SELECT 1 FROM `concerts` WHERE `venue_id` <=> (SELECT id FROM `venues` WHERE `name` <=> 'Killjoy Assembly Hall' AND `city` <=> 'Bandung' AND `country` <=> 'Indonesia') AND `concert_date` <=> '2027-05-22 12:00:00');

INSERT INTO `ticket_types` (`concert_id`, `name`, `price`, `quota`)
SELECT (SELECT id FROM `concerts` WHERE `venue_id` <=> (SELECT id FROM `venues` WHERE `name` <=> 'Killjoy Assembly Hall' AND `city` <=> 'Bandung' AND `country` <=> 'Indonesia') AND `concert_date` <=> '2027-05-22 12:00:00'), 'General Admission', '750000.00', 120
WHERE NOT EXISTS (SELECT 1 FROM `ticket_types` WHERE `concert_id` <=> (SELECT id FROM `concerts` WHERE `venue_id` <=> (SELECT id FROM `venues` WHERE `name` <=> 'Killjoy Assembly Hall' AND `city` <=> 'Bandung' AND `country` <=> 'Indonesia') AND `concert_date` <=> '2027-05-22 12:00:00') AND `name` <=> 'General Admission');

INSERT INTO `ticket_types` (`concert_id`, `name`, `price`, `quota`)
SELECT (SELECT id FROM `concerts` WHERE `venue_id` <=> (SELECT id FROM `venues` WHERE `name` <=> 'Killjoy Assembly Hall' AND `city` <=> 'Bandung' AND `country` <=> 'Indonesia') AND `concert_date` <=> '2027-05-22 12:00:00'), 'Balcony', '1100000.00', 60
WHERE NOT EXISTS (SELECT 1 FROM `ticket_types` WHERE `concert_id` <=> (SELECT id FROM `concerts` WHERE `venue_id` <=> (SELECT id FROM `venues` WHERE `name` <=> 'Killjoy Assembly Hall' AND `city` <=> 'Bandung' AND `country` <=> 'Indonesia') AND `concert_date` <=> '2027-05-22 12:00:00') AND `name` <=> 'Balcony');

INSERT INTO `concerts` (`venue_id`, `concert_date`, `status`)
SELECT (SELECT id FROM `venues` WHERE `name` <=> 'Revenge Auditorium' AND `city` <=> 'Surabaya' AND `country` <=> 'Indonesia'), '2027-05-29 12:30:00', 'scheduled'
WHERE NOT EXISTS (SELECT 1 FROM `concerts` WHERE `venue_id` <=> (SELECT id FROM `venues` WHERE `name` <=> 'Revenge Auditorium' AND `city` <=> 'Surabaya' AND `country` <=> 'Indonesia') AND `concert_date` <=> '2027-05-29 12:30:00');

INSERT INTO `ticket_types` (`concert_id`, `name`, `price`, `quota`)
SELECT (SELECT id FROM `concerts` WHERE `venue_id` <=> (SELECT id FROM `venues` WHERE `name` <=> 'Revenge Auditorium' AND `city` <=> 'Surabaya' AND `country` <=> 'Indonesia') AND `concert_date` <=> '2027-05-29 12:30:00'), 'General Admission', '750000.00', 120
WHERE NOT EXISTS (SELECT 1 FROM `ticket_types` WHERE `concert_id` <=> (SELECT id FROM `concerts` WHERE `venue_id` <=> (SELECT id FROM `venues` WHERE `name` <=> 'Revenge Auditorium' AND `city` <=> 'Surabaya' AND `country` <=> 'Indonesia') AND `concert_date` <=> '2027-05-29 12:30:00') AND `name` <=> 'General Admission');

INSERT INTO `ticket_types` (`concert_id`, `name`, `price`, `quota`)
SELECT (SELECT id FROM `concerts` WHERE `venue_id` <=> (SELECT id FROM `venues` WHERE `name` <=> 'Revenge Auditorium' AND `city` <=> 'Surabaya' AND `country` <=> 'Indonesia') AND `concert_date` <=> '2027-05-29 12:30:00'), 'Balcony', '1100000.00', 60
WHERE NOT EXISTS (SELECT 1 FROM `ticket_types` WHERE `concert_id` <=> (SELECT id FROM `concerts` WHERE `venue_id` <=> (SELECT id FROM `venues` WHERE `name` <=> 'Revenge Auditorium' AND `city` <=> 'Surabaya' AND `country` <=> 'Indonesia') AND `concert_date` <=> '2027-05-29 12:30:00') AND `name` <=> 'Balcony');

INSERT INTO `concerts` (`venue_id`, `concert_date`, `status`)
SELECT (SELECT id FROM `venues` WHERE `name` <=> 'Parade Hall' AND `city` <=> 'Jakarta' AND `country` <=> 'Indonesia'), '2027-06-05 12:00:00', 'cancelled'
WHERE NOT EXISTS (SELECT 1 FROM `concerts` WHERE `venue_id` <=> (SELECT id FROM `venues` WHERE `name` <=> 'Parade Hall' AND `city` <=> 'Jakarta' AND `country` <=> 'Indonesia') AND `concert_date` <=> '2027-06-05 12:00:00');

INSERT INTO `ticket_types` (`concert_id`, `name`, `price`, `quota`)
SELECT (SELECT id FROM `concerts` WHERE `venue_id` <=> (SELECT id FROM `venues` WHERE `name` <=> 'Parade Hall' AND `city` <=> 'Jakarta' AND `country` <=> 'Indonesia') AND `concert_date` <=> '2027-06-05 12:00:00'), 'General Admission', '750000.00', 120
WHERE NOT EXISTS (SELECT 1 FROM `ticket_types` WHERE `concert_id` <=> (SELECT id FROM `concerts` WHERE `venue_id` <=> (SELECT id FROM `venues` WHERE `name` <=> 'Parade Hall' AND `city` <=> 'Jakarta' AND `country` <=> 'Indonesia') AND `concert_date` <=> '2027-06-05 12:00:00') AND `name` <=> 'General Admission');

INSERT INTO `ticket_types` (`concert_id`, `name`, `price`, `quota`)
SELECT (SELECT id FROM `concerts` WHERE `venue_id` <=> (SELECT id FROM `venues` WHERE `name` <=> 'Parade Hall' AND `city` <=> 'Jakarta' AND `country` <=> 'Indonesia') AND `concert_date` <=> '2027-06-05 12:00:00'), 'Balcony', '1100000.00', 60
WHERE NOT EXISTS (SELECT 1 FROM `ticket_types` WHERE `concert_id` <=> (SELECT id FROM `concerts` WHERE `venue_id` <=> (SELECT id FROM `venues` WHERE `name` <=> 'Parade Hall' AND `city` <=> 'Jakarta' AND `country` <=> 'Indonesia') AND `concert_date` <=> '2027-06-05 12:00:00') AND `name` <=> 'Balcony');

INSERT INTO `fan_messages` (`display_name`, `message`)
SELECT 'Parade Listener', 'Every album brings a different memory. I always come back to the songs that made me feel less alone.'
WHERE NOT EXISTS (SELECT 1 FROM `fan_messages` WHERE `display_name` <=> 'Parade Listener' AND `message` <=> 'Every album brings a different memory. I always come back to the songs that made me feel less alone.');

INSERT INTO `fan_messages` (`display_name`, `message`)
SELECT 'Midnight Chorus', 'Nothing beats singing along with friends and hearing everyone join the same chorus.'
WHERE NOT EXISTS (SELECT 1 FROM `fan_messages` WHERE `display_name` <=> 'Midnight Chorus' AND `message` <=> 'Nothing beats singing along with friends and hearing everyone join the same chorus.');

INSERT INTO `fan_messages` (`display_name`, `message`)
SELECT 'Killjoy Journal', 'The music keeps finding a place in my everyday life, from the morning commute to the last song of the night.'
WHERE NOT EXISTS (SELECT 1 FROM `fan_messages` WHERE `display_name` <=> 'Killjoy Journal' AND `message` <=> 'The music keeps finding a place in my everyday life, from the morning commute to the last song of the night.');


-- Verified news summaries, authored for this website; source dates are stated in each article.

INSERT INTO `news_articles` (`title`, `slug`, `content`, `news_date`)
SELECT 'MCR Live in Jakarta: 22 November at JIS', 'mcr-live-jakarta-22-november-2026', 'My Chemical Romance is scheduled to perform at Jakarta International Stadium on 22 November 2026. The date and venue appear on the promoter''s official event poster.

The show is promoted as My Chemical Romance Live in Jakarta. For current ticket information, entry requirements and any schedule changes, consult the promoter directly. The concert date should not be confused with the earlier Hammersonic festival announcement.

Source: Ravel Entertainment / MCR Jakarta
https://www.mcrjakarta.com/
Official event poster: https://www.mcrjakarta.com/mcr1.jpg?key=MCR_JAKARTA2026
Verified: 18 November 2026.', '2026-11-18 13:00:00'
WHERE NOT EXISTS (SELECT 1 FROM `news_articles` WHERE `slug` = 'mcr-live-jakarta-22-november-2026');

INSERT INTO `news_articles` (`title`, `slug`, `content`, `news_date`)
SELECT 'Gerard Way Discusses Recording Plans for 2027', 'gerard-way-recording-plans-2027', 'Metal Hammer reported on 2 November 2026 that Gerard Way discussed making a record in 2027 during My Chemical Romance''s Hollywood Bowl performance on 30 October.

The comments describe the band''s intentions. They do not establish a finished album, a title or a release date. Fans should distinguish plans to record from a confirmed release announcement.

Source: Simon Young, Metal Hammer / Louder
https://www.loudersound.com/bands-artists/my-chemical-romance-to-work-on-album-2027
Source publication: 2 November 2026. Verified: 18 November 2026.', '2026-11-18 12:00:00'
WHERE NOT EXISTS (SELECT 1 FROM `news_articles` WHERE `slug` = 'gerard-way-recording-plans-2027');

INSERT INTO `news_articles` (`title`, `slug`, `content`, `news_date`)
SELECT 'MCR Releases Prelude to the End Short Film', 'mcr-prelude-to-the-end-short-film', 'Pitchfork reported on 29 October 2026 that My Chemical Romance released Prelude to the End, an animated short created with visual artist Lauren Tsai. The voice cast includes Phoebe Bridgers, Billie Eilish, Hideo Kojima and John C. McGinley.

The film incorporates music connected to The Black Parade and arrived around the album''s twentieth-anniversary reissue. View the film through the band''s official YouTube upload.

Source: Hattie Lindert, Pitchfork
https://pitchfork.com/story/phoebe-bridgers-and-billie-eilish-are-in-my-chemical-romances-new-short-film/
Official video: https://www.youtube.com/watch?v=BpJikRTDqdk
Source publication: 29 October 2026. Verified: 18 November 2026.', '2026-11-18 11:00:00'
WHERE NOT EXISTS (SELECT 1 FROM `news_articles` WHERE `slug` = 'mcr-prelude-to-the-end-short-film');

-- Refresh only known starter artwork; custom uploads or other administrator images are retained.
UPDATE `albums` SET `cover_image` = 'assets/albums/bullets.jpg' WHERE `title` = 'I Brought You My Bullets, You Brought Me Your Love' AND (`cover_image` IS NULL OR `cover_image` = 'assets/archive-news.png');
UPDATE `albums` SET `cover_image` = 'assets/albums/three-cheers.jpg' WHERE `title` = 'Three Cheers for Sweet Revenge' AND (`cover_image` IS NULL OR `cover_image` = 'assets/black-parade-inspired.png');
UPDATE `albums` SET `cover_image` = 'assets/albums/black-parade.jpg' WHERE `title` = 'The Black Parade' AND (`cover_image` IS NULL OR `cover_image` = 'assets/Blackparadecover.jpg');
UPDATE `albums` SET `cover_image` = 'assets/albums/danger-days.jpg' WHERE `title` = 'Danger Days: The True Lives of the Fabulous Killjoys' AND (`cover_image` IS NULL OR `cover_image` = 'assets/hero-concert.png');
UPDATE `band_members` SET `cover_img` = 'assets/members/gerard-way.jpg' WHERE `name` = 'Gerard Way' AND (`cover_img` IS NULL OR `cover_img` IN ('assets/archive-news.png', 'assets/Blackparade.png', 'assets/tour-arena.png', 'assets/hero-concert.png'));
UPDATE `band_members` SET `cover_img` = 'assets/members/ray-toro.png' WHERE `name` = 'Ray Toro' AND (`cover_img` IS NULL OR `cover_img` IN ('assets/archive-news.png', 'assets/Blackparade.png', 'assets/tour-arena.png', 'assets/hero-concert.png'));
UPDATE `band_members` SET `cover_img` = 'assets/members/frank-iero.jpg' WHERE `name` = 'Frank Iero' AND (`cover_img` IS NULL OR `cover_img` IN ('assets/archive-news.png', 'assets/Blackparade.png', 'assets/tour-arena.png', 'assets/hero-concert.png'));
UPDATE `band_members` SET `cover_img` = 'assets/members/mikey-way.jpg' WHERE `name` = 'Mikey Way' AND (`cover_img` IS NULL OR `cover_img` IN ('assets/archive-news.png', 'assets/Blackparade.png', 'assets/tour-arena.png', 'assets/hero-concert.png'));

-- Representative official merchandise photos; preserve custom administrator images.
UPDATE products SET image_path='assets/products/parade-tee.png' WHERE name='Parade Emblem Tee' AND category_id=(SELECT id FROM categories WHERE name='Tees') AND image_path IS NULL;
UPDATE products SET image_path='assets/products/revenge-tee.png' WHERE name='Revenge Lettering Tee' AND category_id=(SELECT id FROM categories WHERE name='Tees') AND image_path IS NULL;
UPDATE products SET image_path='assets/products/zip-hoodie.png' WHERE name='Marching Zip Hoodie' AND category_id=(SELECT id FROM categories WHERE name='Outerwear') AND image_path IS NULL;
UPDATE products SET image_path='assets/products/crewneck.png' WHERE name='After Midnight Crewneck' AND category_id=(SELECT id FROM categories WHERE name='Outerwear') AND image_path IS NULL;

-- User-supplied lyrics, 18 November 2026; retain existing administrator edits on reimport.
UPDATE songs SET lyrics='When I was a young boy
My father took me into the city
To see a marching band
He said, "Son, when you grow up
Would you be the savior of the broken
The beaten and the damned?"
He said, "Will you defeat them?
Your demons, and all the non-believers
The plans that they have made?"
"Because one day, I''ll leave you a phantom
To lead you in the summer
To join the black parade"
When I was a young boy
My father took me into the city
To see a marching band
He said, "Son, when you grow up
Would you be the savior of the broken
The beaten and the damned?"
Sometimes I get the feelin''
She''s watchin'' over me
And other times I feel like I should go
When through it all, the rise and fall
The bodies in the streets
And when you''re gone, we want you all to know
We''ll carry on, we''ll carry on
And though you''re dead and gone, believe me
Your memory will carry on, will carry on
And in my heart, I can''t contain it
The anthem won''t explain it
A world that sends you reelin''
From decimated dreams
Your misery and hate will kill us all
So paint it black and take it back
And let''s shout it loud and clear
Defiant to the end, we hear the call
To carry on, we''ll carry on
And though you''re dead and gone, believe me
Your memory will carry on, will carry on
And though you''re broken and defeated
Your weary widow marches
On and on, we carry through the fears
Oh, ah, ha
Disappointed faces of your peers
Oh, ah, ha
Take a look at me, ''cause I could not care at all
Do or die, you''ll never make me
Because the world will never take my heart
Go and try, you''ll never break me
We want it all, we wanna play this part
Won''t explain or say I''m sorry
I''m unashamed, I''m gonna show my scars
Give a cheer for all the broken
Listen here, because it''s who we are
Just a man, I''m not a hero
Just a boy, who had to sing this song
Just a man, I''m not a hero
I don''t care
We''ll carry on, we''ll carry on
And though you''re dead and gone, believe me
Your memory will carry on, you''ll carry on
And though you''re broken and defeated
Your weary widow marches on
Do or die, you''ll never make me
Because the world will never take my heart
Go and try, you''ll never break me
We want it all, we wanna play this part (we''ll carry on)
Do or die, you''ll never make me (we''ll carry on)
Because the world will never take my heart (we''ll carry on)
Go and try, you''ll never break me (we''ll carry on)
We want it all, we wanna play this part (we''ll carry on!)'
WHERE album_id=(SELECT id FROM albums WHERE title='The Black Parade')
AND title='Welcome to the Black Parade' AND (lyrics IS NULL OR lyrics='');

-- Na Na Na lyrics supplied by the user, 18 November 2026; one copy, without duplicated paste.
UPDATE songs SET lyrics='Na, na-na-na, na-na-na, na-na-na-na-na-na
Na, na-na-na, na-na-na, na-na-na-na-na-na
Na, na-na-na, na-na-na, na-na-na
Na-na-na, na-na-na-na
Drugs, gimme drugs, gimme drugs, I don''t need them
But I''ll sell what you got, take the cash, and I''ll keep it
Eight legs to the wall, hit the gas, kill ''em all
And we crawl, and we crawl, and we-
You be my detonator
Love, gimme love, gimme love, I don''t need it
But I''ll take what I want from your heart, and I''ll keep it
In a bag, in a box, with an "X" on the floor
Gimme more, gimme more, gimme more
Shut up and sing it with me
From mall security
To every enemy
We''re on your property
Standing in V formation
Let''s blow an artery
Eat plastic surgery
Keep your apology
Give us more detonation
More
More, gimme more, gimme more
Oh, let me tell you ''bout the sad man
Shut up, and let me see your jazz hands
Remember when you were a madman?
Thought you was Batman
And hit the party with a gas can?
Kiss me, you animal!
You run the company
Fuck like a Kennedy
I think we''d rather be
Burning your information
Let''s blow an artery
Eat plastic surgery
Keep your apology
Give us more detonation
Right here, right now
All the way in Battery City
The little children raise their open, filthy palms
Like tiny daggers up to Heaven
And all the juvie halls
And the Ritalin rats
Ask angels made from neon and f- garbage
Scream out, "What will save us?"
And the sky opened up
Everybody wants to change the world
Everybody wants to change the world
But no one, no one wants to die
Wanna try, wanna try, wanna try, wanna try, wanna try now
I''ll be your detonator
Na, na-na-na, na-na-na, na-na-na-na
Make no apology
It''s death or victory
On my authority
Crash and burn, young and loaded
Drop like a bullet shell
Dress like a sleeper cell
I''d rather go to Hell
Than be in purgatory
Cut my hair, gag and bore me
Pull this pin, let this world explode'
WHERE album_id=(SELECT id FROM albums WHERE title='Danger Days: The True Lives of the Fabulous Killjoys')
AND title='Na Na Na (Na Na Na Na Na Na Na Na Na)' AND (lyrics IS NULL OR lyrics='');

COMMIT;

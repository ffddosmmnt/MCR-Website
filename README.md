# MCR Website

## Konteks proyek kelompok

Proyek ini dikembangkan untuk mata kuliah Web Programming dengan backend **PHP native** dan database **MySQL**. WBS 3.1 menyediakan koneksi PDO bersama dan endpoint baca album; WBS 3.2 menghubungkan Home, Music, News/detail, dan Tour ke database melalui API PHP; tampilan publik sekarang memakai HTML dan JavaScript. Shared Playlist menampilkan daftar lagu/lirik bersama; jadwal konser memakai WIB. Database lokal `mcr_website` dan kecocokannya dengan [skrip MySQL](mcr_database_mysql.db) telah diverifikasi pada 10 November 2026. File tersebut berupa teks SQL meskipun berekstensi `.db`.

- [Pintu masuk aturan agent](AGENTS.md); dokumentasi lengkap dikumpulkan di folder `agent/`.
- [Aturan kerja proyek dan AI](agent/RULES.md)
- [Konteks tim, snapshot jadwal, dan input yang dibutuhkan](agent/PROJECT_CONTEXT.md)
- [Daftar pekerjaan backend untuk agent](agent/BACKEND_TASKS.md)
- [Rencana pengerjaan WBS 3.1–3.5](agent/plan.md) dan [checklist eksekusi](agent/todo.md)
- [Setup PHP/MySQL dan kontrak endpoint](agent/FOUNDATION_SETUP.md), [laporan fondasi 3.1](agent/REPORT_3_1.md)
- [Panduan testing dan perbandingan sebelum/sesudah](agent/TESTING_3_1.md)
- [Pemisahan HTML/PHP dan konfigurasi GitHub Pages](agent/HTML_PHP_SEPARATION.md)
- [Laporan konten dinamis 3.2](agent/REPORT_3_2.md) dan [testing UI/database](agent/TESTING_3_2.md)

Server Python hanya untuk pratinjau statis dan tidak mengeksekusi PHP. Gunakan panduan setup backend untuk menguji API. Sebutan “official web” dalam proyek akademik belum membuktikan afiliasi resmi dengan band.

A multi-page fan website for **My Chemical Romance**, built from editorial wireframes.

Frontend target: [GitHub Pages](https://ffddosmmnt.github.io/MCR-Website/). GitHub Pages serves HTML; live database content requires a separately hosted HTTPS PHP API configured in `site-config.js`. Remote deployment has not been verified.

## Stack

- HTML
- CSS (Arial, Helvetica, sans-serif; shared styles and per-page styles)
- Vanilla JavaScript (no frameworks, no dependencies)
- PHP native with PDO MySQL
- MySQL (local integration verified with 8.4.3)

## Structure

All public pages use HTML, shared `base.css`/`script.js`, and per-page CSS. `content.js` loads Home, Music, Tour, News, and News Detail from read-only PHP JSON endpoints in `api/`. Shared database queries/helpers live in `app/`. About, Store, and Ticket Transaction remain prototypes. Dashboard pages still run on PHP.

## Sections

1. **Home** — Hero, upcoming shows preview, news, and featured stream
2. **About** — Band history, members, timeline, and quotes
3. **Music** — Featured release, discography, searchable lyrics, and fan favorites
4. **Tour** — Tour calendar, VIP packages, past shows, venue info, and fan map
5. **Store** — Merchandise showcase, product filtering, lookbook, and fan bag summary
6. **News** — News updates, newsletter, and article detail
7. **Ticket Transaction** — Seat, delivery, contact, payment, and order summary

## Features

- Accessible responsive navigation with mobile menu, Escape handling, and reduced-motion support
- Song/album/lyric text search with result count and empty state in the loaded catalog
- Responsive YouTube embed with an external fallback link; playback depends on provider availability
- Interactive ticket quantity with minimum/maximum limits
- Dynamic delivery selection, service fee, and checkout total
- Merchandise filtering with an interactive fan bag total
- Database-driven albums, songs/lyrics, shared song list, news/detail and tour summaries
- WIB concert times, empty/error states, safe text rendering and redirects from the five former PHP URLs

## Run

For PHP/MySQL, follow [the backend setup guide](agent/FOUNDATION_SETUP.md). After setting the documented environment variables, run PHP from the repository root with `-d display_errors=0 -S 127.0.0.1:8000 router.php`. Always use the router with the development server so internal files are not served.

For a frontend-only preview, use the restricted local static server below. It serves all public HTML pages without executing PHP; database content needs a running API:

```powershell
python tests/static-preview.py --port 8001
```

Open `http://127.0.0.1:8001/MCR-Website/`. Follow [the separation guide](agent/HTML_PHP_SEPARATION.md) to test separate frontend/backend servers or configure GitHub Pages. The root uses `index.html`. Do not expose the direct-access dashboard publicly without restricting access.

# MCR Website

A multi-page fan website for **My Chemical Romance**, built from editorial wireframes.

## Stack

- HTML
- CSS (Space Grotesk + Inter from Google Fonts)
- Vanilla JavaScript (no frameworks, no dependencies)

## Structure

The project uses shared `base.css` and `script.js` files, plus a dedicated HTML and CSS file for each page.

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
- Live song and album filtering with result count and empty state
- Responsive full-song YouTube embed licensed by Reprise Records
- Interactive ticket quantity with minimum/maximum limits
- Dynamic delivery selection, service fee, and checkout total
- Merchandise filtering with an interactive fan bag total

## Run

Run the project through a local web server so third-party embeds receive an HTTP referrer:

```bash
python3 -m http.server 8000
```

Then open `http://localhost:8000`. Opening the HTML files directly still works for the static content,
but the YouTube player will show a local-server instruction instead of a broken embed.

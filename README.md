# MCR Website

A multi-page fan website for **My Chemical Romance**, built from editorial wireframes.

Website URL: https://ffddosmmnt.github.io/MCR-Website/

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

For the PHP/MySQL features, configure `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and
`DB_PASSWORD` in the process environment, then start the PHP development server from
the repository root with `php -S 127.0.0.1:8000 router.php`.
The active store is `showcase.php`; the ticket flow is `ticket-transaction.php`; and
`news.php` handles newsletter and contact POST requests. The old `.html` URLs redirect
to these active routes when the development router is used.

Ticket orders require an authenticated session exposing `$_SESSION['mcr_user_id']`.
The server calculates price and quota, locks the ticket row, and writes the order and
order item in one transaction. Payment card and CVV data are not collected.

The schema includes `newsletter_subscribers` and `contact_messages`. Set `MAIL_TO` to
an approved local test mailbox to enable notification delivery; without it, submissions
are still stored and the UI explicitly reports that email notification is unavailable.

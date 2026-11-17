# MCR Website

A multi-page fan website for **My Chemical Romance**, built from editorial wireframes.

Website URL: https://ffddosmmnt.github.io/MCR-Website/

## Stack

- HTML
- CSS (Arial/Helvetica/system fonts)
- Vanilla JavaScript (no frameworks, no dependencies)
- Native PHP with PDO MySQL, and MySQL

## Structure

The project uses shared `base.css` and `script.js` files, plus a dedicated HTML and CSS file for each page.

## Sections

1. **Home** — Hero, upcoming shows preview, news, and featured stream
2. **About** — Band history, members, timeline, quotes, and a private contact form
3. **Music** — Featured release, discography, searchable lyrics, and fan favorites
4. **Tour** — Database concert schedule in WIB, venues, and ticket selection
5. **Store** — Merchandise showcase, product filtering, lookbook, and fan bag summary
6. **News** — News updates, article detail, newsletter registration, and selected fan messages
7. **Checkout** — Ticket/merchandise review, merchandise address, and pending order confirmation
8. **Fan Account** — Registration, sign-in, and sign-out

## Features

- Accessible responsive navigation with mobile menu, Escape handling, and reduced-motion support
- Live song and album filtering with result count and empty state
- Responsive YouTube embed with an external viewing link; playback depends on the provider
- Interactive ticket quantity with minimum/maximum limits
- Server-calculated checkout totals with no additional fees
- Merchandise filtering with an interactive fan bag total

## Run

Use the PHP server setup below for the working application. A static server or GitHub Pages can preview the HTML/CSS/JavaScript, but does not execute the PHP APIs or provide accounts and orders by itself.

## PHP/MySQL catalog and checkout

Home, Music, News, Tour, and Store load database content through native PHP JSON APIs. Fan accounts and ticket/merchandise checkout use PHP sessions on the backend host. Orders use IDR with no extra fees and at most two tickets per account per concert across orders. Payment processing is not integrated. Creating a `pending` order reduces stock/quota; cancellation is not available.

Import the original schema from `mcr_database_mysql.db`, then select the intended database and run `migrations/001_checkout_requests.sql`. Existing installations need only the migration. It adds the request-key table used to prevent duplicate orders. Configure `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` in the PHP process environment; `.env` files are not loaded automatically. The backend needs PDO MySQL and mbstring. Run from the project root after setting those variables:

```powershell
php -d display_errors=0 -S 127.0.0.1:8000 router.php
```

Open `http://127.0.0.1:8000/showcase.html`, or create a fan account at `http://127.0.0.1:8000/account.html`. Select merchandise variants or choose a concert from Tour, review the server total, and place an order. Checkout does not collect card details.

GitHub Pages serves the HTML frontend only. Set the public HTTPS API directory in `site-config.js` for hosted database content. With separate frontend/backend hosts, account and checkout pages offer **Continue to checkout**, where the session remains on one origin. The backend must also serve those HTML pages and their assets. Keep database credentials out of JavaScript. Restrict the direct-access administrator dashboard before exposing the PHP server publicly.

## Contact, newsletter, and fan messages

After selecting the application database, import `migrations/002_interactions.sql`. It adds private contacts, newsletter subscriptions, and published fan messages to the same database. Existing tables are preserved.

Visitors can send a private message from About and join the newsletter list on News or article detail. Newsletter registration records the subscription; email delivery is not connected. Fan messages are selected manually by the administrator from external email, then entered through the **Fan messages (published)** dashboard module. Saving publishes the public display name and message on News.

Private contacts and subscriber lists require administrator sign-in at `/dashboard-auth/messages.php`; they are not exposed by the direct dashboard or public content APIs. Include that authenticated dashboard deliberately when preparing deployment; the current local Git ignore settings exclude that directory. No credentials belong in the frontend configuration.

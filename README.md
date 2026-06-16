# MCR Website

A single-page fan website for **My Chemical Romance**, built from a Figma wireframe.

## Stack

- HTML
- CSS (Space Grotesk + Inter from Google Fonts)
- Vanilla JavaScript (no frameworks, no dependencies)

## Structure

```
.
├── index.html    # Single-page site (Home → Tour → Music sections)
├── styles.css    # Design tokens, layout, components
├── script.js     # Scroll-spy nav, cart, modals, lyrics search, etc.
└── .gitignore
```

## Sections

1. **Home** — Hero, upcoming shows preview, news, stream now
2. **Tour** — Full 2025 calendar, VIP packages, past shows, venue info, fan map
3. **Music** — Featured release, discography, searchable lyrics, behind the music, fan favorites

## Features

- Smooth-scroll navigation with active link highlighting (scroll-spy)
- Mobile hamburger menu
- Cart with `localStorage` persistence
- Modals for tickets, VIP packages, news, cart, and global search
- Live lyrics search with match highlighting
- Toast notifications
- Scroll-reveal animations
- Newsletter form with email validation

## Run

Open `index.html` in any browser. No build step required.

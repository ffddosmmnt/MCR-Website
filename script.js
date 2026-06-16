/* =========================================================
   My Chemical Romance — Site Scripts
   Pages: home (index.html) · tour (tour.html) · music (music.html)
   ========================================================= */

(function () {
    'use strict';

    /* ---------- Mock data ---------- */
    const TOUR_DATES = [
        { date: 'MAR 14, 2025', city: 'London',     venue: 'O2 Arena, UK',                  status: 'on-sale',  price: 89 },
        { date: 'MAR 22, 2025', city: 'Berlin',     venue: 'Mercedes-Benz Arena, DE',       status: 'sold-out', price: 0  },
        { date: 'APR 09, 2025', city: 'Tokyo',      venue: 'Saitama Super Arena, JP',       status: 'sold-out', price: 0  },
        { date: 'APR 27, 2025', city: 'Sydney',     venue: 'Qudos Bank Arena, AU',          status: 'on-sale',  price: 95 },
        { date: 'MAY 11, 2025', city: 'Sao Paulo',  venue: 'Allianz Parque, BR',            status: 'on-sale',  price: 79 },
        { date: 'MAY 24, 2025', city: 'Toronto',    venue: 'Scotiabank Arena, CA',          status: 'low',      price: 99 },
        { date: 'JUN 07, 2025', city: 'Mexico City',venue: 'Foro Sol, MX',                  status: 'on-sale',  price: 75 },
        { date: 'JUN 21, 2025', city: 'New York',   venue: 'Madison Square Garden, US',     status: 'low',      price: 109 }
    ];

    const ALBUMS = [
        { title: 'I Brought You My Bullets…', year: 2002, tag: 'DEBUT' },
        { title: 'Three Cheers for Sweet Revenge', year: 2004, tag: 'PLATINUM' },
        { title: 'The Black Parade', year: 2006, tag: 'ICONIC' },
        { title: 'Danger Days', year: 2010, tag: 'CONCEPT' }
    ];

    const FAVORITES = [
        { title: 'Welcome to the Black Parade', plays: '1.2B PLAYS' },
        { title: 'Helena',                       plays: '480M PLAYS' },
        { title: 'I’m Not Okay (I Promise)',     plays: '510M PLAYS' }
    ];

    const LYRICS = [
        { song: 'Welcome to the Black Parade', album: 'The Black Parade', line: 'When I was a young boy, my father took me into the city to see a marching band.' },
        { song: 'Helena', album: 'Three Cheers for Sweet Revenge', line: 'So long and goodnight, so long and goodnight.' },
        { song: 'I’m Not Okay (I Promise)', album: 'Three Cheers for Sweet Revenge', line: 'I’m not okay, I’m not okay, I’m not o-fucking-kay.' },
        { song: 'Teenagers', album: 'The Black Parade', line: 'They’re gonna clean up your looks with all the lies in the books.' },
        { song: 'The Foundations of Decay', album: 'Single', line: 'Get up, coward. See it through. There is no glory in the end.' },
        { song: 'Famous Last Words', album: 'The Black Parade', line: 'I am not afraid to keep on living, I am not afraid to walk this world alone.' },
        { song: 'Cancer', album: 'The Black Parade', line: 'Turn away, if you could get me a drink of water.' },
        { song: 'Mama', album: 'The Black Parade', line: 'Mama, we all go to hell.' }
    ];

    /* ---------- Tiny DOM helper ---------- */
    const $  = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

    document.addEventListener('DOMContentLoaded', () => {
        wireScrollSpy();
        wireMobileMenu();
        renderCartCount();
        wireGlobalClicks();
        wireNewsletter();
        wireReveal();
        initTour();
        initMusic();
        wireModal();
    });

    /* =====================================================
       NAV / SCROLL-SPY / MOBILE MENU / CART
       ===================================================== */
    function wireScrollSpy() {
        const links = $$('.nav-link[data-section]');
        const sections = links
            .map(l => document.getElementById(l.dataset.section))
            .filter(Boolean);
        if (!sections.length) return;

        const setActive = (id) => {
            links.forEach(l => l.classList.toggle('nav-link--active', l.dataset.section === id));
        };

        if ('IntersectionObserver' in window) {
            const io = new IntersectionObserver((entries) => {
                entries.forEach(en => {
                    if (en.isIntersecting) setActive(en.target.id);
                });
            }, { rootMargin: '-40% 0px -55% 0px', threshold: 0 });
            sections.forEach(s => io.observe(s));
        }

        $$('.nav-link[data-section]').forEach(link => {
            link.addEventListener('click', () => {
                $('#navLinks')?.classList.remove('is-open');
            });
        });

        setActive('home');
    }

    function wireMobileMenu() {
        const toggle = $('#menuToggle');
        const list = $('#navLinks');
        if (!toggle || !list) return;
        toggle.addEventListener('click', () => list.classList.toggle('is-open'));
    }

    function getCart()       { try { return JSON.parse(localStorage.getItem('mcr-cart') || '[]'); } catch { return []; } }
    function saveCart(items) { localStorage.setItem('mcr-cart', JSON.stringify(items)); renderCartCount(); }
    function addToCart(item) {
        const cart = getCart();
        cart.push({ ...item, id: Date.now() });
        saveCart(cart);
    }
    function clearCart() { saveCart([]); }

    function renderCartCount() {
        const el = $('#cartCount');
        if (!el) return;
        const count = getCart().length;
        el.textContent = count;
        el.dataset.empty = count === 0;
    }

    /* =====================================================
       GLOBAL CLICK DELEGATION
       ===================================================== */
    function wireGlobalClicks() {
        document.addEventListener('click', (e) => {
            const t = e.target;

            // Cart icon
            if (t.closest('#cartBtn'))  { e.preventDefault(); openCartModal(); return; }
            if (t.closest('#searchBtn')){ e.preventDefault(); openSearchModal(); return; }

            // Tickets
            const ticketBtn = t.closest('.js-tickets');
            if (ticketBtn) { e.preventDefault(); openTicketModal(ticketBtn.dataset.show, +ticketBtn.dataset.price); return; }

            // VIP
            const vipBtn = t.closest('.js-vip');
            if (vipBtn) { e.preventDefault(); openVipModal(vipBtn.dataset.tier, +vipBtn.dataset.price); return; }

            // Stream
            const streamBtn = t.closest('.js-stream');
            if (streamBtn) { e.preventDefault(); toast(`Opening ${streamBtn.dataset.platform}…`); return; }

            // News
            const newsCard = t.closest('.js-news');
            if (newsCard) { e.preventDefault(); openNewsModal(newsCard.dataset.title, newsCard.dataset.body); return; }

            // Play track
            const playBtn = t.closest('.js-play');
            if (playBtn) { e.preventDefault(); toast(`▶ Now playing: ${playBtn.dataset.track}`); return; }

            // Add to cart (generic)
            const cartItem = t.closest('.js-add-cart');
            if (cartItem) { e.preventDefault(); addToCart({ name: cartItem.dataset.item, price: +cartItem.dataset.price }); toast(`Added to cart: ${cartItem.dataset.item}`); return; }
        });
    }

    /* =====================================================
       NEWSLETTER
       ===================================================== */
    function wireNewsletter() {
        const form = $('#newsletterForm');
        if (!form) return;
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const email = form.querySelector('input').value.trim();
            if (!/^\S+@\S+\.\S+$/.test(email)) { toast('Enter a valid email'); return; }
            toast(`Subscribed: ${email}`);
            form.reset();
        });
    }

    /* =====================================================
       REVEAL ON SCROLL
       ===================================================== */
    function wireReveal() {
        const els = $$('.reveal');
        if (!('IntersectionObserver' in window)) {
            els.forEach(el => el.classList.add('is-visible'));
            return;
        }
        const io = new IntersectionObserver((entries) => {
            entries.forEach(en => {
                if (en.isIntersecting) {
                    en.target.classList.add('is-visible');
                    io.unobserve(en.target);
                }
            });
        }, { threshold: 0.12 });
        els.forEach(el => io.observe(el));
    }

    /* =====================================================
       MODAL
       ===================================================== */
    function wireModal() {
        const modal = $('#modal');
        if (!modal) return;
        modal.addEventListener('click', (e) => {
            if (e.target.matches('[data-close]')) closeModal();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });
    }
    function openModal(html) {
        const modal = $('#modal');
        const content = $('#modalContent');
        if (!modal || !content) return;
        content.innerHTML = html;
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        const modal = $('#modal');
        if (!modal) return;
        modal.hidden = true;
        document.body.style.overflow = '';
    }

    /* ---- Specific modals ---- */
    function openTicketModal(show, price) {
        openModal(`
            <p class="modal-meta">TICKETS</p>
            <h2>${show}</h2>
            <p class="modal-price">$${price}</p>
            <p>Standard admission. Includes venue fee. Delivered as mobile ticket 24h before showtime.</p>
            <div class="modal-actions">
                <button class="btn btn--solid" id="confirmBuy">CONFIRM PURCHASE</button>
                <button class="btn btn--outline" data-close>CANCEL</button>
            </div>
        `);
        $('#confirmBuy')?.addEventListener('click', () => {
            addToCart({ name: `Ticket — ${show}`, price });
            closeModal();
            toast(`Ticket added to cart: ${show}`);
        });
    }

    function openVipModal(tier, price) {
        openModal(`
            <p class="modal-meta">VIP PACKAGE</p>
            <h2>${tier}</h2>
            <p class="modal-price">$${price}</p>
            <p>This package includes everything listed in the tier card. VIP confirmations are emailed within 24 hours.</p>
            <div class="modal-actions">
                <button class="btn btn--solid" id="confirmVip">ADD TO CART</button>
                <button class="btn btn--outline" data-close>CANCEL</button>
            </div>
        `);
        $('#confirmVip')?.addEventListener('click', () => {
            addToCart({ name: `VIP — ${tier}`, price });
            closeModal();
            toast(`VIP added: ${tier}`);
        });
    }

    function openNewsModal(title, body) {
        openModal(`
            <p class="modal-meta">NEWS</p>
            <h2>${title}</h2>
            <p>${body}</p>
            <div class="modal-actions">
                <button class="btn btn--outline" data-close>CLOSE</button>
            </div>
        `);
    }

    function openSearchModal() {
        openModal(`
            <p class="modal-meta">SEARCH</p>
            <h2>Search the site</h2>
            <input type="search" id="globalSearch" class="lyrics__input" placeholder="Try 'tour', 'foundations', 'helena'…" style="width:100%; border:1px solid #000; height:48px; padding:0 16px; margin-bottom:16px;">
            <div id="globalSearchResults"></div>
        `);
        const input = $('#globalSearch');
        const out = $('#globalSearchResults');
        input?.focus();
        input?.addEventListener('input', () => {
            const q = input.value.toLowerCase().trim();
            if (!q) { out.innerHTML = ''; return; }
            const hits = [
                ...TOUR_DATES.filter(t => (t.city + t.venue).toLowerCase().includes(q)).map(t => ({ kind: 'Tour', label: `${t.city} — ${t.date}` })),
                ...ALBUMS.filter(a => a.title.toLowerCase().includes(q)).map(a => ({ kind: 'Album', label: `${a.title} (${a.year})` })),
                ...LYRICS.filter(l => (l.song + l.line).toLowerCase().includes(q)).map(l => ({ kind: 'Lyric', label: `${l.song} — ${l.line.slice(0, 60)}…` }))
            ].slice(0, 8);
            out.innerHTML = hits.length
                ? hits.map(h => `<div class="lyric-result"><div class="lyric-result__album">${h.kind}</div><div class="lyric-result__line">${h.label}</div></div>`).join('')
                : `<p class="lyrics__empty">No results for "${q}"</p>`;
        });
    }

    function openCartModal() {
        const cart = getCart();
        const total = cart.reduce((s, i) => s + (Number(i.price) || 0), 0);
        const list = cart.length
            ? cart.map(i => `
                <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #ddd;">
                    <span>${i.name}</span>
                    <span>$${i.price}</span>
                </div>`).join('')
            : '<p class="lyrics__empty">Your cart is empty.</p>';

        openModal(`
            <p class="modal-meta">YOUR CART</p>
            <h2>${cart.length} item${cart.length === 1 ? '' : 's'}</h2>
            <div>${list}</div>
            ${cart.length ? `<p class="modal-price" style="margin-top:16px;">Total: $${total}</p>` : ''}
            <div class="modal-actions">
                ${cart.length ? '<button class="btn btn--solid" id="checkoutBtn">CHECKOUT</button>' : ''}
                ${cart.length ? '<button class="btn btn--outline" id="clearCartBtn">CLEAR CART</button>' : ''}
                <button class="btn btn--outline" data-close>CLOSE</button>
            </div>
        `);
        $('#checkoutBtn')?.addEventListener('click', () => { clearCart(); closeModal(); toast('Order placed! 🤘'); });
        $('#clearCartBtn')?.addEventListener('click', () => { clearCart(); openCartModal(); });
    }

    /* =====================================================
       TOAST
       ===================================================== */
    let toastTimer;
    function toast(msg) {
        const el = $('#toast');
        if (!el) return;
        el.textContent = msg;
        el.hidden = false;
        // force reflow so transition runs
        // eslint-disable-next-line no-unused-expressions
        el.offsetHeight;
        el.classList.add('is-visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            el.classList.remove('is-visible');
            setTimeout(() => { el.hidden = true; }, 250);
        }, 2200);
    }

    function initTour() {
        const body = $('#calendarBody');
        if (!body) return;

        body.innerHTML = TOUR_DATES.map(t => {
            let actionHtml;
            if (t.status === 'sold-out') {
                actionHtml = '<span class="tag tag--muted">SOLD OUT</span>';
            } else if (t.status === 'low') {
                actionHtml = `<button class="btn btn--solid btn--sm js-tickets" data-show="${t.city} — ${t.venue}" data-price="${t.price}">FEW LEFT</button>`;
            } else {
                actionHtml = `<button class="btn btn--solid btn--sm js-tickets" data-show="${t.city} — ${t.venue}" data-price="${t.price}">GET TICKETS — $${t.price}</button>`;
            }
            return `
                <div class="calendar-row">
                    <div class="calendar-row__date">${t.date}</div>
                    <div class="calendar-row__city">${t.city}</div>
                    <div class="calendar-row__venue">${t.venue}</div>
                    <div class="calendar-row__action">${actionHtml}</div>
                </div>
            `;
        }).join('');

        const countEl = $('#tourCount');
        if (countEl) countEl.textContent = `${TOUR_DATES.length} SHOWS`;
    }

    function initMusic() {
        // Discography
        const grid = $('#discographyGrid');
        if (grid) {
            grid.innerHTML = ALBUMS.map(a => `
                <article class="album-card">
                    <div class="album-card__cover placeholder-img"></div>
                    <h3 class="album-card__title">${a.title}</h3>
                    <p class="album-card__year">${a.year} · ${a.tag}</p>
                    <button class="btn btn--outline btn--sm album-card__btn js-play" data-track="${a.title}">PLAY</button>
                    <button class="btn btn--solid btn--sm album-card__btn js-add-cart" data-item="${a.title} (Vinyl)" data-price="32">ADD VINYL — $32</button>
                </article>
            `).join('');
        }

        // Fan favorites
        const fav = $('#favoritesGrid');
        if (fav) {
            fav.innerHTML = FAVORITES.map((f, i) => `
                <div class="fav-card js-play" data-track="${f.title}">
                    <span class="fav-card__rank">${String(i + 1).padStart(2, '0')}</span>
                    <div>
                        <div class="fav-card__title">${f.title}</div>
                        <div class="fav-card__plays">${f.plays}</div>
                    </div>
                </div>
            `).join('');
        }

        // Lyrics search
        const input  = $('#lyricsInput');
        const button = $('#lyricsBtn');
        const results = $('#lyricsResults');
        if (!input || !results) return;

        const runSearch = () => {
            const q = input.value.toLowerCase().trim();
            if (!q) { results.innerHTML = '<p class="lyrics__empty">Type to search across all lyrics.</p>'; return; }
            const hits = LYRICS.filter(l => (l.song + ' ' + l.line + ' ' + l.album).toLowerCase().includes(q));
            if (!hits.length) {
                results.innerHTML = `<p class="lyrics__empty">No matches for "${q}"</p>`;
                return;
            }
            const re = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'ig');
            results.innerHTML = hits.map(l => `
                <div class="lyric-result">
                    <div class="lyric-result__song">${l.song}</div>
                    <div class="lyric-result__album">${l.album}</div>
                    <div class="lyric-result__line">${l.line.replace(re, '<mark>$1</mark>')}</div>
                </div>
            `).join('');
        };

        input.addEventListener('input', runSearch);
        button?.addEventListener('click', runSearch);
        runSearch();
    }

})();

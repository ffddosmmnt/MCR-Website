# MCR Website Design System

## 1. Atmosphere & Identity

An editorial rock archive with stark concert-poster contrast, off-white paper, black rules, and a restrained blood-red accent. The signature is oversized condensed-feeling typography framed by rigid borders and archival imagery.

## 2. Color

| Role | Token | Value | Usage |
|---|---|---|---|
| Paper | `--paper` | `#f5f2ee` | Page and panel backgrounds |
| Soft paper | `--paper-soft` | `#ebe7df` | Secondary surfaces |
| Ink | `--ink` | `#080808` | Primary text and controls |
| Rule | `--line` | `#111111` | Borders and dividers |
| Muted | `--muted` | `#6d6a64` | Secondary copy |
| White | `--white` | `#ffffff` | Inverse text and surfaces |
| Accent | `--accent` | `#8f1111` | Active indicators and links |

Accent is reserved for interactive emphasis. New interface states must reuse these tokens.

## 3. Typography

The project uses Arial/Helvetica/system sans-serif. Display sizes use responsive `clamp()` values, weights 800-900, tight negative tracking, and uppercase labels. Body copy remains at least 14px; compact navigation and metadata may use 11-12px with high weight and generous tracking.

## 4. Spacing & Layout

Spacing follows a 4px base where practical. The shared container is `min(100% - 48px, 1320px)`, reduced to 32px outer space below 980px. Primary responsive breakpoints are 980px and 680px. Layout favors flex and grid with clear black rules rather than decorative shadow.

## 5. Components

### Shared Header

- **Structure**: logo, primary navigation, utility links; JavaScript adds a menu toggle at compact widths.
- **States**: desktop visible, mobile collapsed/open, current link, hover, focus-visible.
- **Accessibility**: toggle exposes `aria-expanded` and `aria-controls`; Escape and link selection close the menu.
- **Motion**: opacity and transform only, 200ms ease-out; reduced-motion removes the transition.

### Searchable Lyrics Table

- **Structure**: labeled search field, submit button, result status, semantic table roles, song rows.
- **States**: all results, filtered results, empty result.
- **Accessibility**: live result count, keyboard-native form controls, empty state announced.
- **Motion**: none; filtering is immediate.

### Ticket Quantity and Summary

- **Structure**: decrement/output/increment control, delivery radios, itemized summary.
- **States**: quantity 1-6, boundary buttons disabled, selected delivery, recalculated total.
- **Accessibility**: descriptive button labels, live quantity and total outputs, native radio controls.
- **Motion**: none; values update immediately.

### Embedded Music Player

- **Structure**: editorial context panel, official full-length audio iframe, and external fallback link.
- **States**: loading, interactive player, provider fallback, and direct-file guidance when HTTP referrer data is unavailable.
- **Accessibility**: iframe has a descriptive title; fallback content explains how to restore the player and opens the same official audio on YouTube in a new tab.
- **Performance**: iframe uses explicit dimensions and native lazy loading to avoid layout shift and defer third-party work.
- **Motion**: none; playback behavior is owned by the provider.

## 6. Motion & Interaction

Micro interactions use 200ms ease-out and animate only `transform` and `opacity`. All controls retain visible keyboard focus. `prefers-reduced-motion: reduce` disables the mobile menu transition.

## 7. Depth & Surface

The surface strategy is borders-first. Cards and controls use 1-3px ink rules; the existing hero imagery may use the shared `--shadow`. The sticky header uses a translucent paper background and backdrop blur.

## 8. Accessibility Constraints & Accepted Debt

Target WCAG 2.2 AA: visible focus, keyboard reachability, semantic labels, 44px touch targets for compact controls, live announcements for dynamic results, and reduced-motion support.

### Accepted Debt

| Item | Location | Why accepted | Owner / Exit |
|---|---|---|---|
| Some existing navigation links target the user-deleted Store page | Shared header/footer | Pre-existing worktree deletion is outside this task | Restore or replace Store page in a separate task |

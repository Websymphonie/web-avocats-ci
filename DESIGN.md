---
version: alpha
name: "Avocat CI Backoffice"
description: "A calm, professional editorial register for Côte d’Ivoire’s legal platform."
colors:
  primary: "oklch(0.7 0.16 258)"
  background: "oklch(0.145 0 0)"
  surface: "oklch(0.205 0 0)"
typography:
  sans:
    fontFamily: "system-ui, sans-serif"
  mono:
    fontFamily: "ui-monospace, monospace"
rounded:
  DEFAULT: "0.625rem"
  sm: "0.375rem"
  md: "0.5rem"
  lg: "0.75rem"
spacing:
  section-gap: "1.5rem"
  page-max: "80rem"
components:
  button: { backgroundColor: "primary", textColor: "#F8FAFC", rounded: "0.5rem", height: "2.5rem" }
  card: { backgroundColor: "surface", rounded: "1rem" }
  dialog: { backgroundColor: "surface", rounded: "0.5rem" }
  table: { textColor: "#F8FAFC" }
  input: { backgroundColor: "background", textColor: "#F8FAFC", rounded: "0.5rem", height: "2.75rem" }
---

# Avocat CI — Design System

## Overview

### Creative North Star

The visual reference is a legal clerk’s register: quiet, dense and dependable, with clear rows, explicit status and deliberate destructive actions. It should feel operational rather than promotional.

### Product context and register

- **Audience and primary job:** Backoffice administrators create, review, publish, archive and remove editorial news and events.
- **Target market(s) and evidence:** Côte d’Ivoire; product context and French-language requirement are documented in `docs/PROJECT_CONTEXT.md` and `docs/UI_UX_GUIDELINES.md`.
- **Locale(s) and language policy:** French UI and content labels; dates use `d/m/Y H:i`.
- **Usage scene:** authenticated desktop-first Backoffice with mobile fallback and moderate information density.
- **Register:** professional operational interface under `/admin/content/news`.
- **Memorable signature:** a restrained register table with status and publication dates kept visible beside one ellipsis action menu.
- **Restraint:** existing shell, semantic tokens, Lucide icons and shared interaction primitives win over visual novelty.
- **Anti-references:** no generic marketing dashboard, no card-only CMS grid, no decorative gradients or unrequested rich editor.
- **Token ownership/runtime mapping:** this file mirrors the canonical tokens in `assets/styles/tailwind.css`; Twig classes consume the semantic Tailwind tokens directly.

## Colors

The dark runtime uses the existing CSS variables: near-black background, slightly raised card surfaces, low-contrast borders, high-contrast foreground and blue primary action. Destructive actions use the existing destructive semantic token. Status badges use success, warning and secondary variants already provided by `Badge`.

## Typography

The application inherits its existing system sans stack. Headings are compact and semibold; list metadata is smaller and muted. French labels use sentence case except for existing table headers, which remain uppercase for scanability.

## Layout

The page reuses the shell’s `lg:pl-72` navigation geometry, a `max-w-7xl` content column, six-unit section rhythm and responsive table-to-card transformation. Search/filter state is kept in the URL; pagination uses the shared partial.

## Elevation & Depth

Hierarchy is conveyed by tonal surfaces, a subtle border and small shadow on cards/popovers. Dark mode avoids bright white panels; dialogs and popovers sit above cards with the shared z-index contract.

## Shapes

The product uses rounded-lg controls and rounded-2xl Backoffice sections, with compact square icon buttons. Dividers are semantic `border-border`; no bespoke radius system is introduced.

## Components

### Foundational visual states

Controls use the existing hover, focus-visible ring, disabled opacity and destructive treatments. Selection is visible through accent-primary checkboxes and a contextual primary-tinted toolbar.

### Buttons and actions

Primary is reserved for create/save. Destructive is separated in `ActionDropdown` and `AlertDialog`. Icon buttons always retain accessible labels; action menus keep text labels for operational clarity.

### Navigation and data display

`ActionDropdown`, `BulkSelection`, `Badge`, shared pagination and the existing Backoffice shell are canonical. Desktop uses the requested columns; mobile uses cards without hiding the action menu.

### Forms and overlays

Symfony Forms own field rendering and validation. `AlertDialog` owns individual and bulk deletion confirmation. `FlashToast` owns post-action feedback. Editorial body fields use the reusable `RichTextEditor` while the Symfony form remains the submission source of truth.

### Rich text content

`RichTextEditor` is the canonical Backoffice editor for semantic editorial HTML. Its toolbar is intentionally limited to paragraphs, H2/H3, inline emphasis, lists, blockquotes, links and history. The editor and rendered content share `.rich-content` styles and semantic theme tokens, so light and dark modes do not introduce a second visual language. Media, uploads, tables and source editing are outside this foundation.

### Multi-select and autocomplete

Symfony UX Autocomplete with Tom Select is the canonical multi-select foundation for Backoffice taxonomy fields. The generic `form-multi-select` hook keeps the control compact, lets chips wrap naturally and gives the dropdown, search input, focus ring and selected options the same semantic token treatment in both themes. Removal stays explicit through the compact × control; inline creation and drag/drop are intentionally excluded.

### Event workflow

Event forms reuse the News editor and taxonomy patterns. Format is a business
choice (`Présentiel`, `En ligne`, `Hybride`) with a small Stimulus layer that
reveals the relevant practical fields; server-side validation remains
authoritative. Listings keep start date, format, status and one ellipsis action
menu visible, with deletion confirmed through the shared bulk/alert primitives.

### Iconography

Lucide icons are used at size-4 with stroke style. Icons support, never replace, French labels on destructive or state-changing actions.

### Motion

Existing short transitions communicate menu/dialog state. No animation is added to routine CRUD work; reduced-motion behavior stays governed by the shared runtime.

### Content and data visualization

Product copy is concise French operational language. Dates use the existing locale format; empty states explain that no news matches the current criteria.

### Photo galleries

Gallery editing follows the same register vocabulary: a responsive image grid,
semantic preview cards, text-alternative and optional-caption fields, a visible
cover marker, and explicit remove actions. Drag and drop is enhanced with
keyboard-reachable Monter/Descendre controls. Dropzones, cards and validation
states use semantic tokens so light and dark themes stay equivalent.

### Document publications

Document screens use the same editorial register and action menu as galleries.
The upload control is a single-file dropzone with a visible filename, detected
type and size summary; the server remains authoritative for acceptance. Access
levels are shown separately from lifecycle status. No document preview or
public viewer is introduced: download is an explicit, permission-aware action.

### Learning COURSE Backoffice

Training administration reuses the Content register primitives while keeping a
separate `Formations` navigation group. The listing makes lifecycle status,
visibility and access type distinct; the form uses the shared rich-text editor
and the public image cover control. Enrollment management uses a dedicated
screen with search, source/status/date columns, explicit grant, revoke
confirmation and reactivation. There is no enrollment bulk delete action.
Member enrollment is available only for published free trainings and protected
resource downloads always pass through the server access policy.

Training classification is presented as a separate Backoffice group with
compact searchable multi-selects for categories and tags. Category and tag
registers reuse the existing table, ActionDropdown, BulkSelection and
AlertDialog patterns; used taxonomies remain visible and cannot be deleted.

### Learning LIVE Backoffice

LIVE reste dans le registre `Formations` avec une création distincte depuis le
bouton Nouvelle formation et un raccourci filtré `Lives`. Le formulaire commun
réutilise la couverture, la visibilité, l’accès et les taxonomies, puis affiche
une section Session Live avec dates, mode, lieu et lien HTTPS. Le mode pilote
les champs visibles via Stimulus, tandis que la validation serveur reste
autoritaire. Le détail LIVE remplace le programme COURSE par Session Live et
Inscriptions ; le lien de connexion n’est jamais présenté sans contrôle
d’accès membre.

The COURSE Programme builder is a nested, inline editor on the training detail
and edit screens. Modules and lessons use compact ellipsis action menus,
explicit AlertDialog confirmation for destructive actions, and visible
positions. Stimulus drag/drop is paired with Monter/Descendre controls so the
structure remains usable by keyboard and on narrow screens. The builder does
not introduce a bulk action bar. The lesson editor adds the shared rich-text
editor, a validated YouTube reference and a private-resource area with
multiple upload, title editing, secure download, reorder drag/drop and
Monter/Descendre keyboard fallback. It uses the same semantic tokens in light
and dark mode.

## Do’s and Don’ts

- **Do:** keep status, publication date and modification date visible in the register.
- **Do:** preserve shared action and confirmation primitives across Backoffice listings.
- **Don’t:** expose status as a free form field or hide destructive actions behind unconfirmed requests.
- **Do:** keep taxonomy selectors grouped after the editorial body and reuse the same register/list primitives for categories and tags.
- **Don’t:** introduce cover images, a rich editor or taxonomy-specific decorative controls without a product requirement.

# CNT-001 — UX contract

## Canonical owners

| Concern | Canonical implementation |
| --- | --- |
| Backoffice shell | `templates/layouts/base.html.twig` |
| Sidebar rail | `templates/layouts/components/sidebar/main_side_bar_component.html.twig` + `assets/styles/app.scss` |
| Row actions | `ActionDropdown` + `NewsTableDropdown` |
| Individual deletion | `DeleteFormComponent` + `AlertDialog` |
| Bulk selection | `BulkSelection` + `BulkDeleteNews` |
| Feedback | `FlashToast` through the shared flash service |
| Forms | Symfony `NewsFormType` |
| Pagination | `shared/views/_list_pagination.html.twig` |
| Authorization | server-side `IsGranted` and `is_granted` with `CONTENT_NEWS_*` |
| Content navigation | `SharedContext\Application\Service\Sidebar\Modules\ContentMenu` |
| Taxonomy listings | `BulkSelection` + `ActionDropdown` in `news_category` and `tag` templates |

## Interaction rules

- The bulk toolbar is hidden until at least one row is selected.
- In the collapsed desktop rail, every navigation icon uses the same 20px box and exact horizontal center, whether its entry opens a submenu or navigates directly.
- The bulk delete label includes the selected count and opens a dynamic confirmation dialog.
- Individual delete always opens a confirmation dialog; no destructive action is submitted during visual verification.
- Publish and archive are explicit POST transitions protected by CSRF and permissions.
- The form never exposes status; lifecycle transitions are action-based.
- Empty, filtered and paginated states preserve the same table/card structure.
- The Content group exposes only Actualités, Catégories d’actualités and Tags in this increment; Events, videos and galleries remain out of scope.
- Category and tag associations are edited from the News form with multi-selects; deleting an item used by News is refused by the backend.

## Accessibility and responsive behavior

Every action has an accessible label, keyboard-focusable controls and visible focus rings. The table exposes a compact mobile card representation so actions remain reachable without horizontal scrolling.

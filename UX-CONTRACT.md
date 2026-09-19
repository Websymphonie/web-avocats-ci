# CNT-001 — UX contract

## Canonical owners

| Concern | Canonical implementation |
| --- | --- |
| Backoffice shell | `templates/layouts/base.html.twig` |
| Sidebar rail | `templates/layouts/components/sidebar/main_side_bar_component.html.twig` + `assets/styles/app.scss` |
| Row actions | `ActionDropdown` + `NewsTableDropdown` / `EventTableDropdown` |
| Individual deletion | `DeleteFormComponent` + `AlertDialog` |
| Bulk selection | `BulkSelection` + `BulkDeleteNews` / `BulkDeleteEvents` |
| Feedback | `FlashToast` through the shared flash service |
| Forms | Symfony `NewsFormType` / `EventFormType` |
| Rich text editor | `assets/react/controllers/Content/RichTextEditor.tsx` mounted by the Symfony News form |
| Multi-select / autocomplete | Symfony UX Autocomplete + Tom Select with the generic `form-multi-select` styling hook |
| Pagination | `shared/views/_list_pagination.html.twig` |
| Authorization | server-side `IsGranted` and `is_granted` with `CONTENT_NEWS_*` / `CONTENT_EVENT_*` |
| Content navigation | `SharedContext\Application\Service\Sidebar\Modules\ContentMenu` |
| Learning navigation | `SharedContext\Application\Service\Sidebar\Modules\LearningMenu` |
| Taxonomy listings | `BulkSelection` + `ActionDropdown` in `news_category`, `event_category` and `tag` templates |
| Training listing | `TrainingTableDropdown` + `BulkSelection` + `AlertDialog` in the Learning Backoffice |
| Training form | `TrainingFormType` + shared `RichTextEditor` + public cover upload |

## Interaction rules

- The bulk toolbar is hidden until at least one row is selected.
- In the collapsed desktop rail, every navigation icon uses the same 20px box and exact horizontal center, whether its entry opens a submenu or navigates directly.
- The bulk delete label includes the selected count and opens a dynamic confirmation dialog.
- Individual delete always opens a confirmation dialog; no destructive action is submitted during visual verification.
- Publish and archive are explicit POST transitions protected by CSRF and permissions.
- The form never exposes status; lifecycle transitions are action-based.
- Empty, filtered and paginated states preserve the same table/card structure.
- The Content group exposes Actualités, Catégories d’actualités, Événements, Catégories d’événements, Vidéos and Tags; public video surfaces and galleries remain out of scope.
- Category and tag associations are edited from the News form with multi-selects; deleting an item used by News is refused by the backend.
- The rich text editor synchronizes its semantic HTML into the Symfony form field; the backend sanitizes the same field on create and update and sanitizes again before Backoffice rendering.
- Link input accepts only `http`, `https`, `mailto`, `tel`, relative and fragment URLs. Rendered links receive safe `rel` attributes; scripts, embeds, styles, event handlers and unsupported tags are removed.
- Taxonomy fields use the existing Symfony UX Autocomplete/Tom Select integration. Chips wrap inside the control, the remove action remains keyboard-reachable through the component behavior, and no inline taxonomy creation is offered in this foundation.
- Multi-select dropdowns, search inputs, selected options and focus states consume the existing semantic tokens; validation errors remain rendered by Symfony Forms below the field.
- Event lifecycle actions are state-aware: publish is available for drafts, cancel for published events, and archive for published or cancelled events. Event status is never a free form field.
- Event practical fields follow the selected format visually, but date ordering, safe URL protocols and publication invariants are always checked server-side.
- News and Event forms expose a shared Media section with direct image upload, preview, replacement/removal and optional single-gallery selection; gallery management remains a link to the dedicated Gallery screen.
- Editorial videos reuse the same listing, action menu, bulk selection and confirmation patterns. The Backoffice preview uses a validated YouTube no-cookie embed only; arbitrary external URLs remain links.
- Galeries photos reuse `ActionDropdown`, `BulkSelection`, `AlertDialog` and the shared feedback service. The listing exposes Couverture, Titre, Images, Statut, Publication and one ellipsis action menu.
- The Gallery editor accepts multiple JPEG/PNG/WebP images, previews selected files, keeps alt text distinct from optional captions, and exposes both drag/drop plus Monter/Descendre fallback controls. A gallery cannot publish until its cover and all alt texts are valid.
- Documents reuse the Content register, ActionDropdown, BulkSelection and AlertDialog. Creation accepts one PDF/DOCX/XLSX/PPTX file, displays name/type/size before submission and does not expose a document preview. Status transitions remain explicit; access level is a separate field.
- Training COURSE reuses the register, action menu, bulk selection and confirmation primitives, but is grouped under `Formations` rather than `Content`. Visibility (`PUBLIC`/`MEMBER`) and access (`FREE`/`PAID`/`RESTRICTED`) are separate fields; status remains transition-based and is never a free form field. The cover is optional and uses the public Media capability under `training/covers`.
- External document downloads use `/documents/{uuid}/download`, are backend-controlled and only serve `PUBLISHED` documents. Backoffice uses `/admin/content/documents/{id}/download`.

## Accessibility and responsive behavior

Every action has an accessible label, keyboard-focusable controls and visible focus rings. The table exposes a compact mobile card representation so actions remain reachable without horizontal scrolling.

# ============================================================

# FILE: docs/ARCHITECTURE.md

# ============================================================

# Avocat CI — Architecture

## 1. Architecture style

The application uses:

- Symfony;
- Bounded Contexts;
- CQRS;
- explicit Domain / Application / Infrastructure / Presenter separation.

The architecture must remain pragmatic.

The goal is maintainability and clear business ownership, not architectural complexity.

---

## 2. Existing contexts

The project already contains:

```text
src/
├── AdminContext/
├── AuthContext/
├── IdentityContext/
├── SharedContext/
├── LogContext/
└── NotificationContext/
└── WebContext/
```

These contexts must be preserved.

---

## 3. Existing context responsibilities

### AdminContext

Responsible for technical and system administration.

It is not automatically responsible for every future business administration
feature of the Avocat CI platform.

---

### AuthContext

Responsible for authentication workflows.

---

### IdentityContext

Responsible for application users, identity and authorization concepts.

Member and administrator accounts and application roles should reuse this
context.

---

### SharedContext

Contains genuinely shared technical concepts.

Business concepts must not be placed in SharedContext without a strong reason.

---

### LogContext

Responsible for application/activity logging according to existing implementation.

---

### NotificationContext

Responsible for notifications.

First-version notifications should remain simple.

Prefer internal notifications before introducing external channels.

---

### WebContext

Public website.

---

## 4. Candidate business contexts

The following contexts represent the intended business boundaries.

They must not all be created immediately.

A context should only be introduced when its first required feature is implemented.

---

## Production Security Checklist

The repository provides safe local defaults only. Before production deployment,
the following values and controls must be supplied by the deployment
environment:

- official `APP_URL` using the production HTTPS origin;
- `SECURE_SCHEME=https`;
- explicit trusted proxy IPs/CIDRs matching the real reverse-proxy topology;
- explicit trusted hosts for the production domains;
- an active TLS certificate and HTTP-to-HTTPS redirect at the edge;
- `cookie_secure=true` for production sessions;
- `APP_DEBUG=0`;
- application secrets externalized from the repository;
- HSTS, security headers and CSP evaluated and enabled from the infrastructure
  after validation.

The local `APP_URL` and `SECURE_SCHEME` values are not production deployment
claims. Production proxy addresses and hostnames must not be guessed in the
application configuration.

---

## 6. Context structure

A context may use:

```text
src/
└── <Context>/
    ├── Application/
    │   ├── Service/
    │   ├── Event/
    │   └── UseCase/
    │       ├── Command/
    │       ├── CommandHandler/
    │       ├── Query/
    │       └── QueryHandler/
    │
    ├── Domain/
    │   ├── Enum/
    │   ├── Exception/
    │   ├── Event/
    │   ├── Model/
    │   └── Repository/
    │
    ├── Infrastructure/
    │   ├── Listener/
    │   ├── Persistence/
    │   │   └── Doctrine/
    │   │       ├── Entity/
    │   │       └── Repository/
    │   ├── Factory/
    │   └── Validator/
    │
    └── Presenter/
        ├── Component/
        ├── Controller/
        ├── Form/
        ├── Service/
        └── Twig/
```

Directories are created only when required.

---

## 7. Dependency direction

Allowed:

```text
Presenter -> Application -> Domain
Infrastructure -> Domain
```

Forbidden:

```text
Domain -> Doctrine
Domain -> Symfony
Domain -> Presenter
Domain -> Infrastructure
Domain -> Twig
```

---

## 8. Cross-context references

Avoid sharing Doctrine entities between contexts.
Prefer stable identifiers and application-level collaboration according to existing project conventions.

---

## 9. Commands and Queries

Use Commands for writes.

Use Queries for reads.

Examples:

```text
CreateContentCommand
UpdateContentCommand
PublishContentCommand
CreateTrainingCommand
CreateEnrollmentCommand
RecordPaymentCommand
```

Queries:

```text
ListPublishedContentQuery
GetTrainingDetailsQuery
GetMemberEnrollmentQuery
GetContributionSituationQuery
GetPaymentDetailsQuery
```

Names must follow existing project conventions if they differ.

---

## 10. UI ownership

Templates and controllers related to a business context belong to that context's Presenter layer.

Generic visual components may be shared only when genuinely reusable.

Les conventions permanentes d’interface sont centralisées dans
[`docs/UI_UX_GUIDELINES.md`](UI_UX_GUIDELINES.md). Symfony Forms/Twig est le
chemin par défaut ; Stimulus porte les interactions JavaScript classiques et
Symfony UX React est réservé aux dashboards et visualisations analytiques qui
justifient une interface riche. Les calculs métier et l’autorisation restent
côté Symfony.

### Surfaces Web

Les surfaces de présentation sont explicites et ne constituent pas de nouveaux
Bounded Contexts :

- **Public / Frontoffice** : porté par `WebContext`, accessible anonymement sur
  les routes publiques explicitement déclarées. La page d’accueil `/` est la
  surface publique actuellement implémentée ; les futurs contenus publics ne
  sont pas considérés comme développés par cette formalisation.
- **Member Area** : portée par l’authentification et l’identité existantes,
  accessible sous `/espace` aux utilisateurs authentifiés. Cette surface ne
  crée pas de `MemberContext`.
- **Backoffice** : conservé sous `/admin`, avec les contrôleurs existants dans
  leurs contextes. `AdminContext` reste dédié à ses responsabilités actuelles ;
  les fonctionnalités métier administrées restent à la charge de leur
  contexte propriétaire lorsqu’elles seront introduites.

Les routes non déclarées publiques sont protégées par défaut. La visibilité
publique future d’une fiche ou d’un catalogue ne vaut pas autorisation d’accès
à un contenu protégé.

---

## 11. Architecture decision principle

When implementing a new feature:

```text
Business responsibility
        ↓
Existing context?
   ↙ yes      no ↘
reuse       justify new context
```

## 12. MediaContext minimal — CNT-004

`MediaContext` est introduit uniquement pour stocker les images publiques de
galeries. Il porte les métadonnées techniques (`originalName`, nom sûr généré,
MIME réel, taille, dimensions et chemin local), la validation serveur et le
stockage. Son contrat de stockage est local aujourd’hui, sous
`public/uploads/galleries`; il n’introduit ni S3, ni médiathèque, ni relation
Doctrine vers `ContentContext`.

Les variantes sont rendues par LiipImagine à partir de l’original maîtrisé. Les
fichiers privés, documents et médias Learning restent hors de cette capacité.

# ============================================================

# FILE: docs/PERMISSIONS.md

# ============================================================

# App — Permissions

## 1. Purpose

This document defines the initial functional access model.

Actual Symfony role names should reuse existing IdentityContext conventions whenever possible.

Do not create duplicate security systems.

---

## 2. Business profiles

Initial business profiles:

```text
Manager
Avocat
Utilisateur
```

Suggested technical role names only if equivalent roles do not already exist:

```text
ROLE_ADMIN
ROLE_AVOCAT
ROLE_USER
```

Do not rename existing roles without justification.

## 2.1 Supported application roles

The active role catalog is limited to the following roles:

```text
ROLE_SUPER_ADMIN
ROLE_ADMIN
ROLE_AVOCAT
ROLE_USER
```

Historical roles are retained only when required to read existing accounts or
legacy data. They are not offered for new assignments or permission
configuration.

`ROLE_COMPTABLE` couvre les fonctions financières actuellement disponibles (échéances, encaissements et quittances). Ses
droits effectifs restent
configurables par le Super Admin ; il ne reçoit pas `PAYMENT_REVERSE` par
défaut.

---

## 3. Permission levels

Legend:

```text
MANAGE = create/update and operational actions
VIEW   = read only
NONE   = no normal access
```

---

## 4. Initial matrix — legacy inherited reference

The following matrix is retained only as historical project documentation. Its
old operational vocabulary is not an Avocat CI product requirement and must not
be used to design new Content, Learning, Contribution or Payment features.
The active Avocat CI role catalog and current surface rules are defined in
sections 2.1 and 5.

| Capability        | Direction | Agency Manager | Technical Manager | Commercial        | Customer Relations | Executive Assistant |
|-------------------|-----------|----------------|-------------------|-------------------|--------------------|---------------------|
| Users             | MANAGE    | VIEW           | NONE              | NONE              | NONE               | NONE                |
| Owners            | MANAGE    | MANAGE         | VIEW              | MANAGE            | VIEW               | VIEW                |
| Properties        | MANAGE    | MANAGE         | VIEW              | MANAGE            | VIEW               | VIEW                |
| Prospects         | VIEW      | MANAGE         | NONE              | MANAGE            | VIEW               | VIEW                |
| Prospecting       | VIEW      | MANAGE         | NONE              | MANAGE            | NONE               | NONE                |
| Visits            | VIEW      | MANAGE         | NONE              | MANAGE            | MANAGE             | VIEW                |
| Customer files    | VIEW      | MANAGE         | NONE              | MANAGE            | MANAGE             | VIEW                |
| Tenants           | VIEW      | MANAGE         | VIEW              | VIEW              | MANAGE             | MANAGE              |
| Leases            | VIEW      | MANAGE         | NONE              | VIEW              | MANAGE             | MANAGE              |
| Inspections       | VIEW      | MANAGE         | VIEW              | VIEW              | MANAGE             | VIEW                |
| Rent follow-up    | VIEW      | MANAGE         | NONE              | NONE              | VIEW               | MANAGE              |
| Rent payments     | VIEW      | MANAGE         | NONE              | NONE              | VIEW               | MANAGE              |
| Rent arrears      | VIEW      | MANAGE         | NONE              | NONE              | VIEW               | MANAGE              |
| Complaints        | VIEW      | MANAGE         | MANAGE            | VIEW              | MANAGE             | MANAGE              |
| Interventions     | VIEW      | MANAGE         | MANAGE            | VIEW              | MANAGE             | VIEW                |
| Providers         | VIEW      | MANAGE         | MANAGE            | NONE              | VIEW               | VIEW                |
| Quotes            | VIEW      | MANAGE         | MANAGE            | NONE              | VIEW               | VIEW                |
| Provider invoices | VIEW      | MANAGE         | MANAGE            | NONE              | VIEW               | MANAGE              |
| Documents         | MANAGE    | MANAGE         | MANAGE            | MANAGE            | MANAGE             | MANAGE              |
| Reports           | VIEW      | VIEW           | VIEW              | VIEW own/relevant | VIEW relevant      | VIEW relevant       |

---

## 5. Important rules

This matrix is an initial functional reference.

Before implementing authorization:

1. inspect existing IdentityContext roles;
2. map existing roles to these responsibilities;
3. avoid duplicate roles;
4. use backend authorization.

Twig visibility is not sufficient authorization.

### Actualités — CNT-001

Les permissions dédiées suivantes sont disponibles dans `PermissionEnum` et
sont accordées par défaut à `ROLE_ADMIN` lorsque le rôle ne possède pas encore
de configuration persistée :

```text
CONTENT_NEWS_VIEW
CONTENT_NEWS_MANAGE
CONTENT_NEWS_PUBLISH
CONTENT_NEWS_DELETE
```

`CONTENT_NEWS_MANAGE` couvre la création et la modification ; la publication et
l’archivage relèvent de `CONTENT_NEWS_PUBLISH`. Une configuration de rôle déjà
persistée reste prioritaire et doit être ajustée explicitement par un Super
Admin si nécessaire.

### Taxonomies éditoriales — CNT-001A

Les permissions suivantes sont accordées par défaut à `ROLE_ADMIN` uniquement
(`ROLE_SUPER_ADMIN` conserve toutes les permissions) :

```text
CONTENT_NEWS_CATEGORY_VIEW
CONTENT_NEWS_CATEGORY_MANAGE
CONTENT_NEWS_CATEGORY_DELETE
CONTENT_TAG_VIEW
CONTENT_TAG_MANAGE
CONTENT_TAG_DELETE
```

Elles ne sont pas accordées par défaut à `ROLE_AVOCAT` ni `ROLE_USER`.

### Événements — CNT-002

Les permissions dédiées suivantes sont accordées par défaut à `ROLE_ADMIN`
uniquement :

```text
CONTENT_EVENT_VIEW
CONTENT_EVENT_MANAGE
CONTENT_EVENT_PUBLISH
CONTENT_EVENT_CANCEL
CONTENT_EVENT_DELETE
CONTENT_EVENT_CATEGORY_VIEW
CONTENT_EVENT_CATEGORY_MANAGE
CONTENT_EVENT_CATEGORY_DELETE
```

Elles ne sont pas accordées par défaut à `ROLE_AVOCAT` ni `ROLE_USER`.

### Surfaces de présentation

Les surfaces Web sont séparées du catalogue des rôles :

- `/` et les routes publiques explicitement déclarées sont accessibles avec
  `PUBLIC_ACCESS` ;
- `/espace` requiert `IS_AUTHENTICATED_FULLY` ;
- `/admin` et ses sous-routes requièrent une authentification, puis les
  contrôleurs appliquent leurs autorisations propres. Le tableau de bord
  Backoffice est réservé à `ROLE_ADMIN` et `ROLE_SUPER_ADMIN` via la hiérarchie
  existante.

Une ressource peut être publiquement visible sans que son contenu protégé soit
accessible sans autorisation serveur.

For Visits, `VISIT_MANAGE` is granted to Super Admin, Agency Manager, Commercial and Customer Relations. `VISIT_VIEW` is
granted to Super Admin, Direction, Agency Manager, Commercial, Customer Relations and Executive Assistant. Technical
Manager has no Visit access.

---

## 6. Ownership restrictions

The first version does not require complex row-level authorization.

Examples not required initially:

```text
Commercial A can never see Commercial B's prospects
```

unless explicitly requested.

Simple role-based access is preferred.

---

## 7. Direction

Direction may generally access all operational data.

System-level administration may still require existing higher technical roles such as `ROLE_SUPER_ADMIN` depending on
current project implementation.

Business Direction must not automatically receive every technical/system permission.

---

## 8. Future refinement

Permissions may later become more granular.

Do not build a complete dynamic ACL/permission engine unless the client explicitly requires it.

## 8. Configuration persistée

Le système distingue clairement les responsabilités suivantes :

- `RoleGroupEnum` identifie un groupe ou un rôle historique utilisé par l'application ;
- `PermissionEnum` constitue le vocabulaire stable des permissions techniques et métier ;
- `RolePermissions` persiste la configuration choisie pour un rôle ;
- `DefaultRolePermissions` fournit uniquement les permissions initiales lorsqu'aucune configuration persistée n'existe.

Une configuration persistée est entièrement prioritaire, y compris lorsqu'elle contient zéro permission. Le système ne
fusionne jamais une configuration persistée avec les valeurs par défaut : une révocation reste donc effective
immédiatement.

Le rôle `ROLE_SUPER_ADMIN` conserve toutes les permissions connues, sans nécessiter de ligne en base, et ne peut pas
être modifié depuis `/admin/roles`.

## 9. Vocabulaire métier configurable — legacy inherited reference

The permission codes in this section are retained to read existing stored
configuration. They are not a specification for new Avocat CI business
features. New permissions require an explicit product and architecture
decision.

Le périmètre HTTP applicatif est privé par défaut. Les routes publiques sont
explicitement listées dans la configuration de sécurité (connexion et
réinitialisation du mot de passe) ; une nouvelle route métier doit donc rester
authentifiée et conserver son contrôle de permission métier.

Les décisions d’autorisation ne doivent jamais être mises en cache dans un état
statique partagé. Les redirections basées sur une entrée navigateur doivent
être limitées à des destinations internes validées.

Les permissions métier reconnues par le stockage existant sont :

```text
OWNER_VIEW OWNER_MANAGE
PROPERTY_VIEW PROPERTY_MANAGE PROPERTY_RESERVE PROPERTY_WORK_MANAGE
PROSPECT_VIEW PROSPECT_MANAGE
VISIT_VIEW VISIT_MANAGE
CUSTOMER_FILE_VIEW CUSTOMER_FILE_MANAGE CUSTOMER_FILE_DECIDE
RENTAL_APPLICATION_VIEW RENTAL_APPLICATION_MANAGE RENTAL_APPLICATION_DECIDE
TENANT_VIEW TENANT_MANAGE
LEASE_VIEW LEASE_MANAGE
INSPECTION_VIEW INSPECTION_MANAGE
RENT_DUE_VIEW RENT_DUE_MANAGE
PAYMENT_VIEW PAYMENT_MANAGE PAYMENT_REVERSE
RENT_RECEIPT_VIEW RENT_RECEIPT_ISSUE
RENT_NOTICE_VIEW RENT_NOTICE_ISSUE
RENT_REMINDER_VIEW RENT_REMINDER_ISSUE
EXPENSE_VIEW EXPENSE_MANAGE EXPENSE_PAY EXPENSE_CANCEL EXPENSE_REIMBURSE
EXPENSE_REFERENCE_MANAGE
OWNER_STATEMENT_VIEW OWNER_STATEMENT_MANAGE OWNER_STATEMENT_FINALIZE OWNER_STATEMENT_SEND
OWNER_PAYOUT_RECORD OWNER_PAYOUT_REVERSE
AGENCY_COMMISSION_VIEW AGENCY_COMMISSION_MANAGE
FINANCE_DASHBOARD_VIEW
FINANCE_REPORT_VIEW
```

`OWNER_STATEMENT_SEND` couvre l'envoi explicite asynchrone des relevés
propriétaires et de leurs justificatifs de reversement. Il ne donne pas le
droit de consulter un document : le téléchargement reste contrôlé par
`OWNER_STATEMENT_VIEW`.

Les dépenses sont consultables par `DIRECTION`, `AGENCY_MANAGER`,
`TECHNICAL_MANAGER`, `EXECUTIVE_ASSISTANT` et `COMPTABLE`. Leur création et
modification sont accordées à `AGENCY_MANAGER`, `TECHNICAL_MANAGER`,
`EXECUTIVE_ASSISTANT` et `COMPTABLE`. Une configuration `RolePermissions`
persistée reste prioritaire et n’est pas migrée automatiquement.

Les matrices de ce document sont des valeurs initiales configurables, et non des droits métier immuables. Les
contrôleurs, les vues Twig et la navigation utilisent la même résolution persistée.

### Paiements de formation — PAY-001

Les permissions livrées sont :

```text
PAYMENT_VIEW
PAYMENT_OFFER_VIEW
PAYMENT_OFFER_MANAGE
```

ROLE_ADMIN reçoit ces trois permissions dans les defaults ; ROLE_SUPER_ADMIN
conserve son accès global. ROLE_AVOCAT et ROLE_USER ne les reçoivent pas par
défaut. Une configuration RolePermissions persistée reste prioritaire, comme
pour les autres permissions configurables.

PAY-001 ne fournit aucune permission de confirmation manuelle : les paiements
Backoffice sont consultables en lecture seule et la confirmation passe par un
flux serveur/provider futur.

PAY-002 n’ajoute pas de permission webhook. /webhook/kkiapay est une surface
technique publique protégée par x-kkiapay-secret et la vérification serveur
KkiaPay, sans session ni CSRF. Les clés KkiaPay restent des secrets
d’environnement et ne sont jamais exposées au Backoffice ou au navigateur,
à l’exception de la clé publique destinée au widget.

`EXPENSE_REFERENCE_MANAGE` autorise la gestion des catégories et fournisseurs du référentiel Finance. Par défaut, cette
permission est accordée à `ROLE_SUPER_ADMIN`, `ROLE_AGENCY_MANAGER` et `ROLE_COMPTABLE`. Les autres rôles ne la
reçoivent pas automatiquement et les configurations `RolePermissions` persistées restent prioritaires.

`EXPENSE_PAY` et `EXPENSE_CANCEL` sont distinctes de la gestion courante des
dépenses. Elles sont accordées par défaut à `ROLE_SUPER_ADMIN`,
`ROLE_AGENCY_MANAGER` et `ROLE_COMPTABLE`. Elles ne sont pas accordées par
défaut aux autres rôles. Les configurations persistées restent autoritaires.

Les permissions `RENT_NOTICE_VIEW` et `RENT_NOTICE_ISSUE` concernent respectivement la consultation et l’émission des
avis d’échéance. Elles sont distinctes des permissions d’échéances, de paiements et de quittances. Par défaut,
`ROLE_COMPTABLE` possède les deux permissions.

Les permissions `RENT_REMINDER_VIEW` et `RENT_REMINDER_ISSUE` concernent respectivement la consultation de l’historique
et l’enregistrement manuel des relances de loyer. Par défaut, `ROLE_COMPTABLE` et `ROLE_CUSTOMER_RELATIONS` possèdent
les deux permissions ; `ROLE_DIRECTION` possède uniquement la consultation. Elles restent configurables et autoritaires
lorsqu’une configuration persistée existe.

L’automatisation des relances est une politique système sans permission utilisateur dédiée. Les permissions existantes
restent applicables à la consultation, à l’enregistrement manuel et à l’envoi manuel.

`RENT_REMINDER_SEND` autorise la transmission email d’une relance existante. Par défaut, cette permission est accordée à
`ROLE_SUPER_ADMIN`, `ROLE_AGENCY_MANAGER`, `ROLE_CUSTOMER_RELATIONS`, `ROLE_EXECUTIVE_ASSISTANT` et `ROLE_COMPTABLE`,
mais pas à `ROLE_DIRECTION`, `ROLE_TECHNICAL_MANAGER` ni `ROLE_COMMERCIAL`. Elle n’est pas ajoutée automatiquement aux
configurations persistées existantes.

La migration `Version20260911000000` complète une seule fois les configurations historiques génériques des rôles métier
avec leurs defaults métier. Les rôles qui contiennent déjà un code métier connu ne sont pas fusionnés silencieusement.
Les marqueurs legacy de `ROLE_USER` et `ROLE_COURIER` restent stockés et ignorés. Après cette migration, une sauvegarde
depuis `/admin/roles` devient pleinement autoritaire.

`EXPENSE_REIMBURSE` autorise l’enregistrement des remboursements d’avances
employés. Par défaut, elle est accordée à `ROLE_SUPER_ADMIN`,
`ROLE_AGENCY_MANAGER` et `ROLE_COMPTABLE`.

`AGENCY_COMMISSION_VIEW` autorise la consultation des règles historisées de
commission d’agence et `AGENCY_COMMISSION_MANAGE` leur configuration. Par
défaut, la consultation est accordée à `ROLE_SUPER_ADMIN`, `ROLE_DIRECTION`,
`ROLE_AGENCY_MANAGER` et `ROLE_COMPTABLE`; la modification est limitée à
`ROLE_SUPER_ADMIN`, `ROLE_DIRECTION` et `ROLE_AGENCY_MANAGER`. Les permissions
persistées restent prioritaires.

`FINANCE_DASHBOARD_VIEW` autorise l’accès aux agrégats du tableau de bord Finance (encaissements, dépenses agence,
commissions, relevés et reversements). Par
défaut, cette permission est accordée à `ROLE_SUPER_ADMIN`, `ROLE_DIRECTION`,
`ROLE_AGENCY_MANAGER` et `ROLE_COMPTABLE`. Les liens vers les écrans détaillés
restent soumis à leurs permissions métier propres. Une configuration persistée,
y compris `[]`, demeure entièrement prioritaire.

`FINANCE_REPORT_VIEW` autorise l’accès aux rapports financiers opérationnels,
dont le rapport détaillé des encaissements. Par défaut, elle est accordée à
`ROLE_SUPER_ADMIN`, `ROLE_DIRECTION`, `ROLE_AGENCY_MANAGER` et `ROLE_COMPTABLE`.
Les actions et liens vers les paiements restent soumis à `PAYMENT_VIEW` ; une
configuration persistée, y compris `[]`, demeure entièrement prioritaire.

### Vidéos éditoriales — CNT-003

Les permissions `CONTENT_VIDEO_VIEW`, `CONTENT_VIDEO_MANAGE`,
`CONTENT_VIDEO_PUBLISH` et `CONTENT_VIDEO_DELETE` sont accordées par défaut à
`ROLE_ADMIN` uniquement. Elles ne sont pas accordées par défaut à
`ROLE_AVOCAT` ni `ROLE_USER`.

### Galeries photos — CNT-004

`CONTENT_GALLERY_VIEW`, `CONTENT_GALLERY_MANAGE`, `CONTENT_GALLERY_PUBLISH` et
`CONTENT_GALLERY_DELETE` sont accordées par défaut à `ROLE_ADMIN` et à
`ROLE_SUPER_ADMIN` (via son comportement global). Elles ne sont pas accordées
par défaut à `ROLE_AVOCAT` ni `ROLE_USER`. Les configurations persistées de la
matrice de rôles restent prioritaires.

### Documents — CNT-005

Les permissions `CONTENT_DOCUMENT_VIEW`, `CONTENT_DOCUMENT_MANAGE`,
`CONTENT_DOCUMENT_PUBLISH`, `CONTENT_DOCUMENT_DELETE` et
`CONTENT_DOCUMENT_RESTRICTED_DOWNLOAD` sont accordées par défaut à
`ROLE_ADMIN` et `ROLE_SUPER_ADMIN`. `ROLE_AVOCAT` et `ROLE_USER` ne les
reçoivent pas par défaut. Le téléchargement `PUBLIC` ne dépend pas de ces
permissions ; les autres niveaux restent contrôlés côté serveur.

### Formations COURSE — LRN-001

Les permissions `LEARNING_TRAINING_VIEW`, `LEARNING_TRAINING_MANAGE`,
`LEARNING_TRAINING_PUBLISH` et `LEARNING_TRAINING_DELETE` sont accordées par
défaut à `ROLE_ADMIN` et à `ROLE_SUPER_ADMIN` via son comportement global.
Elles ne sont pas accordées par défaut à `ROLE_AVOCAT` ni `ROLE_USER`. Les
configurations persistées de la matrice de rôles restent prioritaires.

La verticale livrée concerne l’administration Backoffice des formations
`COURSE`, de leur structure modules/leçons, de leur contenu pédagogique et des
transitions prévues. Les ressources de leçon restent couvertes par la même
permission ; aucune permission Media générique n’est ajoutée. Les permissions
`LEARNING_ENROLLMENT_VIEW` et `LEARNING_ENROLLMENT_MANAGE` sont accordées par
défaut à `ROLE_ADMIN` et `ROLE_SUPER_ADMIN`, mais pas à `ROLE_AVOCAT` ni
`ROLE_USER`. L’auto-inscription membre et la policy de lecture ne dépendent
pas de ces permissions Backoffice. LIVE réutilise ces permissions Learning ;
les paiements et la progression restent hors périmètre.

### Taxonomies Learning — LRN-004A

`LEARNING_CATEGORY_VIEW`, `LEARNING_CATEGORY_MANAGE` et
`LEARNING_CATEGORY_DELETE`, ainsi que `LEARNING_TAG_VIEW`,
`LEARNING_TAG_MANAGE` et `LEARNING_TAG_DELETE`, sont accordées par défaut à
`ROLE_ADMIN` et `ROLE_SUPER_ADMIN`. Elles ne sont pas accordées par défaut à
`ROLE_AVOCAT` ni `ROLE_USER`. Ces permissions ne contrôlent jamais l’accès aux
formations.

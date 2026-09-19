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

`MediaContext` est introduit uniquement pour stocker les images publiques
nécessaires aux galeries, couvertures éditoriales et couvertures de formations.
Il porte les métadonnées techniques (`originalName`, nom sûr généré, MIME réel,
taille, dimensions et chemin local), la validation serveur et le stockage. Sa
racine persistante unique est configurée par `APP_STORAGE_DIR` (valeur canonique
`/shared/storage`) et ses adaptateurs dérivent `/shared/storage/public/galleries`
pour les galeries, `/shared/storage/public/content/covers` pour les couvertures
News/Event et `/shared/storage/public/training/covers` pour les couvertures
Training;
l’exposition HTTP publique attendue est `/uploads/<storage-key>` et ne doit
jamais contenir le chemin absolu. Sur un déploiement neuf, la stratégie
préférée est une seule exposition `public/uploads -> /shared/storage/public`.
Le dépôt conserve toutefois `public/uploads` pour les anciens uploads
Vich/Admin : jusqu’à leur migration, l’infrastructure doit exposer au minimum
`/uploads/galleries` et `/uploads/content/covers` vers les deux sous-répertoires
persistants. `private/` ne doit disposer d’aucun alias ou lien HTTP. Les clés
persistées en base restent relatives (`galleries/<nom>` ou
`content/covers/<nom>` ou `training/covers/<nom>`). Il n’introduit ni S3, ni
médiathèque, ni relation Doctrine vers un contexte métier. Les consommateurs
signalent leurs usages via le contrat applicatif partagé de vérification média.

Les variantes sont rendues par LiipImagine à partir de l’original maîtrisé. Les
fichiers privés, documents et médias pédagogiques Learning restent hors de
cette capacité; seule la couverture publique de la fiche Training est livrée
dans LRN-001.

## 12.1 LearningContext — structure COURSE LRN-002

La structure pédagogique minimale est répartie dans deux tables dédiées :
course_module et lesson. Les entités Doctrine conservent un trainingId et un
moduleId scalaires ; elles ne chargent pas de relation Doctrine vers Training
ni entre tous les niveaux. Les repositories et le guard applicatif valident
explicitement l’appartenance au bon Training COURSE.

Les réordonnancements vérifient côté application que la liste reçue est
complète, sans doublon et sans identifiant étranger. La persistence utilise
une phase temporaire transactionnelle avant d’écrire les positions finales afin
de respecter les contraintes d’unicité (training_id, position) et
(module_id, position). La suppression d’un module supprime explicitement ses
leçons puis normalise les positions restantes.

Le builder Backoffice utilise Twig, Symfony Forms et Stimulus pour le drag &
drop, avec les boutons Monter et Descendre comme alternative clavier. Il
n’introduit ni React, ni BulkSelection pour les modules/leçons. Les mutations
sont protégées par CSRF et LEARNING_TRAINING_MANAGE.

## 12.2 LearningContext — contenu pédagogique LRN-003

Le contenu d’une `Lesson` est nettoyé par le service Tiptap/sanitizer partagé.
La vidéo est une référence externe YouTube : seul son identifiant est utilisé
pour l’embed, sans appel API ni hébergement vidéo privé. `LessonResource`
conserve un `storedFileId` scalaire et ne crée aucune relation Doctrine vers
`MediaContext`.

Les ressources privées utilisent la racine persistante configurée
`APP_STORAGE_DIR`, sous `private/learning/resources`; elles ne sont pas
placées sous `var/` et ne possèdent aucun alias public. Les formats autorisés
sont PDF, DOCX, XLSX et PPTX, avec validation MIME réelle, extension
cohérente, taille maximale configurable et nom de stockage aléatoire. La
suppression est DB-first puis best-effort sur le fichier physique, et le
nettoyage est refusé lorsqu’un `StoredFile` est encore utilisé par un document
ou une ressource Learning.

## 12.3 LearningContext — inscriptions LRN-004

`Enrollment` est une persistence Learning indépendante : `trainingId` et
`userId` sont des identifiants scalaires, sans relation Doctrine vers Identity.
`UserDirectoryInterface` fournit uniquement un read model utilisateur pour le
Backoffice. `TrainingAccessPolicy` centralise les contrôles utilisateur actif,
formation publiée et inscription active. Les commandes d’auto-inscription,
d’attribution admin et de révocation sont idempotentes et ne suppriment jamais
physiquement une inscription.

Le Backoffice expose `/admin/learning/trainings/{trainingId}/enrollments` avec
recherche, attribution, révocation et réactivation. Le membre dispose de
`POST /espace/learning/trainings/{uuid}/enroll` pour les formations gratuites
et de téléchargements protégés par
`/espace/learning/resources/{resourceUuid}/download`. La résolution complète
ressource → leçon → module → formation précède l’autorisation. Une formation
ayant des inscriptions ne peut pas être supprimée.

## 12.4 LearningContext — catégories et tags LRN-004A

`TrainingCategory` et `TrainingTag` appartiennent exclusivement à
`LearningContext`. Ce sont deux taxonomies plates, facultatives et distinctes
du `ContentContext\Tag` : elles ne participent ni à l’autorisation ni à la
politique d’accès aux formations.

Les associations `Training` ↔ catégories/tags sont persistées par deux tables
ManyToMany dédiées. Le domaine ne dépend que d’identifiants scalaires ; les
entités Doctrine et les tables de jointure restent dans l’infrastructure
Learning. Les commandes de création et de mise à jour valident explicitement
chaque identifiant demandé et refusent les associations inconnues.

Les catégories et tags utilisés ne sont pas supprimables. Une suppression en
lot ignore les éléments encore utilisés et retourne le résultat de l’opération
sans provoquer d’erreur serveur. Les listes Backoffice exposent leurs propres
filtres, tandis que le listing Training peut combiner une catégorie et un tag
avec ses filtres existants. Aucun menu ou stockage de médiathèque partagé n’est
introduit par cette tranche.

## 13. Documents privés — CNT-005

`ContentContext` ne stocke qu’un `storedFileId` scalaire et ne référence pas
l’entité Doctrine `StoredFile`. `MediaContext` valide le MIME réel avec
`finfo`, la taille configurable (20 Mio par défaut), l’extension cohérente et
la lisibilité, puis génère une clé relative aléatoire et un checksum SHA-256
sous une racine hors webroot. Les documents sont dérivés sous
`/shared/storage/private/documents`, avec des clés relatives
(`documents/<nom>`). La suppression physique est explicite et refusée si une
publication la référence. `DocumentDownloadPolicy` autorise uniquement les
publications `PUBLISHED` : `PUBLIC` est anonyme, `MEMBER` requiert un compte,
`RESTRICTED` requiert en plus la permission dédiée et `PRIVATE` reste
Backoffice.

### Provisionnement du stockage persistant

Chaque environnement doit définir `APP_STORAGE_DIR` et fournir une racine
persistante avec les droits d’écriture du processus PHP. Le provisionnement
initial est idempotent :

```bash
mkdir -p "$APP_STORAGE_DIR/public/galleries" \
         "$APP_STORAGE_DIR/public/content/covers" \
         "$APP_STORAGE_DIR/private/documents"
```

La configuration préférée d’un déploiement neuf est :

```text
public/uploads -> $APP_STORAGE_DIR/public
```

Lorsque `public/uploads` contient encore les anciens uploads Vich/Admin, ne pas
le remplacer sans migration : utiliser des alias ou liens spécialisés pour
`/uploads/galleries` et `/uploads/content/covers`, puis planifier la migration.
Les documents privés ne doivent avoir aucun alias HTTP direct.

Checklist minimale :

```text
APP_STORAGE_DIR=/shared/storage
$APP_STORAGE_DIR/public writable
$APP_STORAGE_DIR/private writable
public/uploads -> $APP_STORAGE_DIR/public (ou aliases spécialisés temporaires)
$APP_STORAGE_DIR/private non exposé par le serveur web
base de données sauvegardée avec $APP_STORAGE_DIR
```

Les uploads compensent une erreur de persistence en supprimant le fichier
nouvellement stocké lorsque c’est possible. Pour une suppression, la ligne DB
est supprimée d’abord, puis le fichier physique est nettoyé au mieux ; un
échec de filesystem est journalisé comme fichier orphelin à traiter plus tard.
Cette opération n’est pas artificiellement présentée comme une transaction
atomique DB/filesystem.

`var/` reste réservé aux caches, logs et fichiers runtime. Il ne doit contenir
aucun document métier ni image persistante. Les sauvegardes doivent inclure
`$APP_STORAGE_DIR` avec la base de données, et toute restauration doit
reconstituer les deux sous-répertoires avant de rendre l’application active.

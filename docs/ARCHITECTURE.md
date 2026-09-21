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

Responsible for technical application logging and the specialized authentication
history according to the existing implementation.

The current `Logs` persistence is not the canonical business audit trail:

```text
Monolog
    -> technical operational logs
LogContext::Logs
    -> SQL persistence for selected technical logs
AuthLog
    -> authentication history
```

`DbLogListener` captures scalar, sanitized information from Doctrine lifecycle
events. `DbLogHandler` persists through DBAL without recursively flushing the
Doctrine entity manager. SQL logging is best-effort: a technical logging
failure must not make an otherwise valid business mutation fail. Passwords,
password hashes, tokens, provider secrets, authorization headers, cookies,
sessions and CSRF values must never be persisted in `Logs` context or extra
data.

This is deliberately distinct from the business audit contract. `LOG-002`
defines the actor, action, target, metadata and immutability foundation.
`LOG-003` connects selected Content, Learning, Payment and Identity events
without making those contexts depend on `LogContext`.

The business audit trail is now a separate append-only capability:

`BT`
Business context event producer
    -> RecordAuditEntryCommand
    -> LogContext::AuditEntry
`BT`

`AuditEntry` stores scalar actor and target references only. It has no Doctrine
relation to `User` or to a business target, so it remains readable after those
records are removed. Metadata is intentional, simple JSON sanitized by the
LOG-001 policy, and deduplication is protected by a nullable unique key.
Normal application workflows expose no update or delete operation for audit
entries. The Backoffice audit screen is read-only and uses the current `LOGS`
group, currently restricted to `ROLE_SUPER_ADMIN`.

The audit persistence transaction is separate from the producer business
transaction. The current pipeline is synchronous post-commit and best-effort:
an audit persistence failure is logged technically and does not turn an
already-committed business mutation into a user-facing failure. There is no
outbox, retention policy or compliance-grade guaranteed delivery.

The current mapping is implemented by separate `ContentAuditSubscriber`,
`LearningAuditSubscriber`, `PaymentAuditSubscriber` and
`IdentityAuditSubscriber` classes. They consume scalar producer events and
are the only place that builds the audit mapping.

---

### NotificationContext

Responsible for notifications.

First-version notifications should remain simple.

Prefer internal notifications before introducing external channels.

---

### EventEmitterFeature et notifications — état actuel

`EventEmitterFeature` appartient à
`SharedContext\Domain\Service\EventDispatcher`. Il stocke en mémoire une liste
d'objets via `emitEvent()` ; `releaseEvents()` retourne les événements dans
l'ordre d'émission puis vide le tampon. Il est utilisé par les agrégats qui
doivent publier un fait métier, notamment Identity, Learning et Payment.

Le contrat `SharedContext\Domain\Service\EventDispatcher\EventDispatcher` est
adapté par `SymfonyEventDispatcher`, qui transmet chaque événement au
`EventDispatcherInterface` Symfony immédiatement et séquentiellement. Ce
pipeline n'utilise pas Messenger et ne possède pas d'outbox.

Le pipeline de notification transactionnelle est synchrone et best-effort :

```text
Learning/Payment/Identity event
    -> NotificationSubscriber (Infrastructure)
    -> SendNotificationUseCase
    -> NotificationsService
    -> notifications
```

Les événements producteurs restent propriétaires de leur contexte et ne
transportent que des scalaires. Le commit métier intervient avant le dispatch
du fait ; le listener de notification journalise une panne et ne transforme
pas une réussite Learning/Payment déjà enregistrée en erreur utilisateur.
Les notifications transactionnelles privées utilisent une clé de
déduplication SHA-256 persistée et protégée par une contrainte unique. Aucun
Outbox, event store ou transport Messenger asynchrone n'est introduit par
NOT-001. Une Outbox pourra être étudiée si une garantie de livraison devient
un besoin métier.

La persistance des notifications est réalisée dans une transaction Doctrine
propre à l'opération. Les événements sont dispatchés après la persistence du
use case producteur ; un échec du listener ne peut donc pas annuler cette
mutation déjà validée. Les emails d'activation et de réinitialisation suivent
des listeners séparés et le `Mailer` synchrone ; ils ne sont pas produits par
`NotificationContext`.

La commande « tout marquer comme lu » reste limitée aux notifications privées
ciblées par utilisateur. Le modèle de lecture des notifications publiques et
leur état lu/non lu global constituent une dette séparée, volontairement hors
scope de NOT-REV-002.

### Dettes techniques acceptées après CORE-FIX-001

- `Logs` et `Notification` conservent encore des relations Doctrine vers
  l’entité `IdentityContext\User`; leur découplage nécessite une migration et
  des read models dédiés et reste hors de ce ticket ;
- l’état lu/non lu global des notifications publiques reste à définir ;
- aucune durée de rétention n’est inventée pour les logs techniques ou l’audit
  métier ;
- les noms historiques `isSuccessFulAuth` et `Logouth` d’`AuthLog` sont
  conservés pour éviter une migration cosmétique.

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
  les routes publiques explicitement déclarées. La homepage, les actualités,
  les événements, le catalogue public des formations et la recherche globale
  constituent les premières surfaces publiques livrées ; les autres contenus
  restent soumis à leur verticale dédiée.
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

## 10.1 Contrôle d’accès des contrôleurs Backoffice

Chaque contrôleur d’un module métier exposé dans le Backoffice doit déclarer
son périmètre de groupe métier au niveau de la classe, par exemple avec
HasGroupAccess(RoleGroupEnum::NEWS).

Les groupes dédiés livrés couvrent notamment NEWS, CATEGORY_NEWS, EVENTS,
CATEGORY_EVENTS, VIDEOS, GALLERIES, DOCUMENTS, TRAININGS, COURSE_MODULES,
ENROLLMENTS, CATEGORY_TRAININGS, TAG_TRAININGS, PAYMENTS et PAYMENT_OFFERS.
Ces groupes sont réservés à SUPER_ADMIN et ADMIN. Ce contrôle de groupe
constitue la barrière d’accès à la surface Backoffice.
Le contrôle fin de l’opération reste ensuite porté par la permission métier
appropriée (*_VIEW, *_MANAGE, *_PUBLISH, *_DELETE, etc.). Les contrôleurs
Member ne reçoivent pas ces groupes : ils utilisent l’authentification membre
et leurs règles d’accès propres.

Lorsqu’un contrôleur porte aussi un `HasGroupAccess` au niveau d’une méthode,
cet attribut est prioritaire sur celui de la classe. En l’absence d’attribut
sur la méthode, celui de la classe est appliqué. Les groupes ne sont pas
combinés implicitement.

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
course_module et lesson. L’API applicative conserve des identifiants scalaires
trainingId et moduleId pour ne pas exposer de graphe Doctrine ; l’infrastructure
porte néanmoins les relations internes nécessaires aux clés étrangères
`training_id -> training.id` et `module_id -> course_module.id`. Les
repositories et le guard applicatif valident explicitement l’appartenance au
bon Training COURSE.

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

Les sessions `LIVE` peuvent également porter une référence externe YouTube
facultative (`streamProvider` et `externalStreamId`) sans relation Doctrine
vers un fournisseur ou vers `MediaContext`. La référence est normalisée côté
Learning et rendue dans l’espace membre via `youtube-nocookie.com/embed`; elle
ne déclenche aucune API YouTube. Le `joinUrl` reste une destination HTTPS
générique, résolue exclusivement par une route membre autorisée et jamais
injectée directement dans Twig.

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
`userId` restent des identifiants scalaires dans l’API de contexte, sans
relation Doctrine vers Identity. L’infrastructure porte uniquement la clé
étrangère interne `training_id -> training.id`; `user_id` ne possède aucune
contrainte cross-context.
`UserDirectoryInterface` fournit uniquement un read model utilisateur pour le
Backoffice et l’éligibilité. `TrainingLearnerEligibility` est l’autorité
Learning unique : l’utilisateur doit être actif et porter explicitement
`ROLE_AVOCAT`. `ROLE_ADMIN` et `ROLE_SUPER_ADMIN` ne sont pas apprenants par
défaut. `TrainingAccessPolicy` réutilise cette décision avec les contrôles de
formation publiée et d’inscription active. Les commandes d’auto-inscription,
d’attribution admin et de révocation sont idempotentes et ne suppriment jamais
physiquement une inscription.

Les suppressions bulk prévalident toute la sélection avant d’exécuter une seule
suppression. Les clés étrangères internes sur `course_module`, `lesson`,
`lesson_resource`, `enrollment` et `live_training_details` sont restrictives ;
la suppression applicative retire explicitement la structure COURSE avant la
formation. Aucun FK n’est créé vers Identity, Media ou StoredFile.

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

## 12.5 LearningContext — Training LIVE LRN-005

`TrainingType::LIVE` réutilise l’aggregate `Training`; aucun `LiveTraining`
parallèle n’est introduit. Les champs spécifiques résident dans
`LiveTrainingDetails`, persisté séparément dans `live_training_details` avec
une contrainte d’unicité sur `training_id`. Le domaine Learning porte la
validation des dates et du mode de diffusion (`ONLINE`, `IN_PERSON`, `HYBRID`),
dont les exigences de lieu et de lien HTTPS.

La commande de publication appelle les règles existantes de `Training` : les
COURSE conservent leur readiness modules/leçons et les LIVE vérifient leurs
détails de session. Le listing et le formulaire Backoffice restent unifiés,
avec une création explicite Cours/Live et un raccourci `/admin/learning/lives`.
Les routes de structure COURSE utilisent `CourseStructureGuard` et refusent
les LIVE.

L’accès au lien externe passe par la query applicative
`GetAccessibleLiveJoinDetailsQuery` et `TrainingAccessPolicy`, puis par
`GET /espace/learning/trainings/{uuid}/join`. Le client ne fournit jamais la
destination de redirection. Les APIs Zoom, Teams, Google Meet et YouTube,
l’attendance, le calendrier et le replay restent hors scope.

Le formulaire HTML sérialise les modes avec leurs valeurs métier
`ONLINE`/`IN_PERSON`/`HYBRID`; le contrôleur et le domaine restent l’autorité de
la validation. Une session `IN_PERSON` répond de manière contrôlée sans
redirection externe.

## 12.6 LearningContext — progression apprenant LRN-006

`LessonProgress` appartient à `LearningContext` et conserve uniquement les
identifiants scalaires `enrollmentId` et `lessonId`. La table
`lesson_progress` possède une unicité sur `(enrollment_id, lesson_id)` et des
clés étrangères internes vers `enrollment` et `lesson`; aucune relation vers
Identity n’est créée. Les entités Doctrine restent confinées à
l’infrastructure et sont converties en modèles de domaine.

Les mutations membre sont `POST /espace/learning/lessons/{uuid}/start` et
`/complete`, protégées par authentification, CSRF et `TrainingAccessPolicy`.
La résolution UUID → leçon → module → formation précède le contrôle de
l’inscription, ce qui protège contre l’IDOR. Les lectures de structure peuvent
exposer l’état de chaque leçon; le résumé de cours et le listing des
inscriptions utilisent un agrégat SQL groupé pour éviter le N+1.

Le Backoffice affiche une synthèse en lecture seule pour les `COURSE`, et
`—` pour les `LIVE`. Les suppressions de leçon ou module sont refusées dès
qu’une progression existe. La progression est conservée pendant une révocation
et restaurée à la réactivation. Le lecteur apprenant, l’auto-complétion par
vidéo, les quiz et les certificats restent hors périmètre.

## 12.7 PaymentContext — fondation PAY-001

PaymentContext possède TrainingOffer et Payment dans sa propre persistence.
TrainingOffer référence un trainingId scalaire et conserve le montant actif en
devise ISO ; Payment conserve un snapshot du montant et de la devise, son
fournisseur, sa référence, son statut et sa clé d’idempotence. La seule
relation Doctrine interne est payment.training_offer_id vers training_offer.id.

Les ports TrainingCatalogInterface, ActiveTrainingEnrollmentCheckerInterface,
PaidTrainingBuyerEligibilityInterface et PaidTrainingAccessGranterInterface
évitent à l’Application Payment de manipuler la persistence Learning ou de
connaître directement `ROLE_AVOCAT`. L’adaptateur Infrastructure Payment
délègue l’éligibilité à `TrainingLearnerEligibility`. Les adaptateurs actuels
utilisent Learning et FakePaymentGateway. La confirmation exige la référence
fournisseur attendue, persiste d’abord le fait financier et son
`fulfillmentStatus=PENDING`, puis tente l’activation à Learning via le port ;
le frontend ne constitue jamais une preuve de paiement.

La devise appartient au référentiel existant d’AdminContext. Payment expose
un port applicatif CurrencyCatalogInterface, alimenté par un adaptateur
Infrastructure, afin que ses formulaires et handlers utilisent uniquement les
devises actives. TrainingOffer conserve le code ISO scalaire validé, sans
relation Doctrine vers l’entité Currencies; Payment snapshotte ce code au
moment de l’initiation. Aucune table, migration ou médiathèque de devises
n’est créée par PAY-001A.

PAY-001 conserve Fake pour les tests. KkiaPay, son SDK PHP officiel, le
checkout membre minimal et le webhook Symfony sont livrés par PAY-002/PAY-002B.
La certification réelle Sandbox reste une recette externe.

### 12.8 PaymentContext — KkiaPay et webhook PAY-002

KkiaPay est sélectionné par configuration (PAYMENT_PROVIDER=KKIAPAY) et
derrière PaymentGatewayInterface; PAYMENT_PROVIDER=FAKE reste la valeur de
test. Le SDK PHP officiel est encapsulé dans
`KkiaPaySdkClient`, derrière `KkiaPaySdkClientInterface`, et n’est utilisé que
par `KkiaPayTransactionVerifier`. La sélection sandbox/production est
transmise au constructeur SDK depuis `KKIAPAY_SANDBOX`.

`KkiaPayTransactionVerifier` transforme immédiatement la réponse SDK en
`VerifiedPaymentTransaction`. Il mappe explicitement les statuts provider
`SUCCESS` et `FAILED`; un statut `PENDING` reste rejouable et les autres
statuts techniques ne sont jamais convertis silencieusement en échec métier.
Le SDK expose réellement `transactionId`, `partnerId`, `amount` et `status`;
la réponse vérifiée ne fournit pas systématiquement `currency`, qui reste donc
optionnelle et n’est comparée que lorsqu’elle est effectivement présente.
PAY-002B utilise `kkiapay/kkiapay-php` depuis `dev-master`, dont la révision
exacte est figée dans `composer.lock`.

Symfony Webhook expose /webhook/kkiapay. KkiaPayRequestParser exige POST,
JSON, x-kkiapay-secret valide et les champs minimaux avant de produire un
RemoteEvent. KkiaPayWebhookConsumer résout le Payment par son UUID
(partnerId), vérifie la transaction côté serveur et ne dispatch les
commandes Payment qu’après comparaison du montant, de la référence et de la
devise lorsque celle-ci est fournie. Le consumer est synchrone tant qu’un
worker asynchrone supervisé n’est pas garanti.

La surface membre redirige vers une page de statut serveur. Le widget ne
reçoit que KKIAPAY_PUBLIC_KEY, le montant snapshot et le partnerId; ses
callbacks ne mutent jamais Payment. Les clés privées et le secret webhook
restent côté serveur.

### 12.10 PAY-003 — fulfillment Payment → Learning

Payment sépare désormais le statut financier du traitement métier post-paiement :

```text
PaymentStatus
    PENDING -> CONFIRMED
    PENDING -> FAILED

PaymentFulfillmentStatus
    null avant confirmation ou après échec financier
    PENDING après confirmation provider
    COMPLETED après activation Learning réussie
```

Une confirmation provider est persistée dans Payment avant l’appel au port
`PaidTrainingAccessGranterInterface`. Les deux contextes ne partagent pas de
transaction Doctrine et Payment ne référence aucune entité Learning. Une erreur
Learning laisse donc durablement `CONFIRMED/PENDING`, est journalisée avec un
compteur de tentative et peut être rejouée sans modifier le fait financier.

`FulfillConfirmedPaymentHandler` est utilisé par la confirmation et par la
commande opérationnelle :

```bash
php bin/console app:payment:reconcile-fulfillment
php bin/console app:payment:reconcile-fulfillment --payment=<uuid>
```

La commande ne contacte pas KkiaPay. Elle traite uniquement les paiements
confirmés dont le fulfillment n’est pas terminé. Le grant Learning reste
idempotent grâce à l’unicité Training/User existante. Une activation réussie
produit l’événement Learning et sa notification selon les règles existantes.
Une confirmation déjà complète est un no-op.

Les listings Payment et TrainingOffer lisent les résumés Training via le port
batch `TrainingCatalogInterface::getByIds()`. Le port retourne un read model
scalaire et n’expose aucune entité Doctrine Learning à Payment. Une référence
Training manquante n’interrompt pas le listing : l’interface affiche
`Formation indisponible`.

### 12.9 PAY-002A — certification Sandbox et exploitation

La certification Sandbox est une recette externe, sans secret dans Git. Pour
l'activer, l'environnement doit fournir `KKIAPAY_PUBLIC_KEY`,
`KKIAPAY_PRIVATE_KEY`, `KKIAPAY_SECRET_KEY`, `KKIAPAY_WEBHOOK_SECRET` et
`KKIAPAY_SANDBOX=true`, avec `PAYMENT_PROVIDER=KKIAPAY`. Seule la clé publique
est transmise au navigateur; les autres valeurs restent côté serveur et ne
doivent pas apparaître dans les logs.

Le webhook doit être configuré dans le dashboard KkiaPay sur une URL HTTPS
accessible : `/webhook/kkiapay`, avec les événements
`transaction.success` et `transaction.failed` et le même secret que
`KKIAPAY_WEBHOOK_SECRET`. Le traitement actuel est synchrone : la route
Symfony exécute le parseur puis le consumer; aucun routage
`ConsumeRemoteEventMessage` vers un worker asynchrone n'est déclaré.

`Payment.currency` reste le snapshot de `TrainingOffer.currency`. Le consumer
compare la devise uniquement si la réponse de vérification KkiaPay fournit un
champ `currency` non vide; sinon il ne prétend pas effectuer une vérification
de devise absente du contrat provider.

La mise en production exige au minimum :

```text
KKIAPAY_SANDBOX=false
clés production configurées hors Git
webhook production HTTPS configuré
secret webhook synchronisé
transaction.success et transaction.failed activés
permissions RolePermissions synchronisées
worker configuré uniquement si le webhook devient asynchrone
```

La recette réelle reste `NOT EXECUTED` tant que des clés Sandbox et une URL
HTTPS publiquement accessible ne sont pas provisionnées dans l'environnement
de recette.

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

Chaque déploiement doit définir `APP_STORAGE_DIR` et fournir une racine
persistante avec les droits d’écriture du processus PHP. En développement local,
si la variable n’est pas définie, le fallback est `<project>/storage`, à la
racine du dépôt et volontairement hors de `var/` afin de survivre au nettoyage
du cache. Le provisionnement initial est idempotent :

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

LiipImagine lit les originaux depuis `$APP_STORAGE_DIR/public`. Comme les
URLs publiques historiques conservent le préfixe `/uploads`, le provisionnement
doit également fournir sous `$APP_STORAGE_DIR/public/uploads` les alias
`content`, `training` et `galleries` vers leurs répertoires frères. Cela permet
aux miniatures de résoudre le même chemin que celui exposé par le serveur web,
sans recopier les images ni les placer dans `var/`.

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

## 14. Pages statiques — CNT-006

`Page` appartient à `ContentContext` et est persistée dans la table `page`.
Elle possède un titre, un slug unique, un contenu riche nettoyé côté serveur,
un statut `DRAFT` ou `PUBLISHED` et les dates éditoriales. Les commandes et
queries de page restent séparées du modèle Doctrine ; le Backoffice utilise
`HasGroupAccess(RoleGroupEnum::PAGES)` et les permissions `CONTENT_PAGE_*`.

Depuis CNT-006A, la page peut aussi porter `coverMediaId`, une référence
scalaire nullable vers un média public. Il n'existe aucune relation Doctrine
cross-context ; le stockage, la validation serveur, les noms générés et les
URLs réutilisent les capacités existantes de `MediaContext` sous
`/shared/storage/public/content/covers`. La couverture est facultative pour la
création et la publication, et le composant Backoffice existant gère son upload,
aperçu, remplacement et retrait. Le vérificateur d'usage Content empêche la
suppression d'un média encore utilisé par une Page.

Aucun écran Frontoffice final ni route publique n’est ajouté par CNT-006/A. La query
`FindPublishedPageBySlug` expose seulement un DTO de lecture et ne permet de
retourner qu’une page publiée ; son DTO restitue la couverture lorsqu'elle existe.
Les événements de cycle de vie réutilisent
`ContentLifecycleEvent` et produisent les actions d’audit
`content.page.published`, `content.page.unpublished` et `content.page.deleted`.

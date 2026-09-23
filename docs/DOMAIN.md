# Avocat CI — Domaines et frontières

## 1. Règle de lecture

Ce document décrit le modèle cible sans déclarer que tous les domaines sont
déjà implémentés.

Un Bounded Context n’est créé qu’au moment où une première fonctionnalité
réelle justifie sa frontière. Il ne faut pas créer un contexte par entité, page,
menu ou route.

Les domaines métier doivent conserver leurs propres règles et ne doivent pas
partager directement leurs entités Doctrine. Les échanges passent par des
identifiants, contrats applicatifs, Commands/Queries ou événements selon le
besoin.

## 2. Contextes existants

Les contextes suivants existent déjà et doivent être préservés :

| Contexte | Responsabilité actuelle |
|---|---|
| `AdminContext` | administration technique et système existante |
| `AuthContext` | login, logout, mot de passe et workflows d’authentification |
| `IdentityContext` | utilisateurs, identité, rôles et autorisation existants |
| `SharedContext` | services techniques réellement transverses |
| `LogContext` | logs techniques, authentification et audit métier structuré |
| `NotificationContext` | mécanismes de notification existants |
| `WebContext` | shell, recherche et capacités du site public existant |

`AdminContext` n’est pas le propriétaire automatique de toutes les fonctions
accessibles sous `/admin`. Une fonction métier future reste dans le contexte qui
porte sa règle.

## 3. Contextes métier cibles

| Contexte candidat | Responsabilité cible | Statut |
|---|---|---|
| `ContentContext` | contenus éditoriaux publics et leur administration | `IMPLEMENTED` — backend/Backoffice livré, Frontoffice différé |
| `LearningContext` | formations, contenus pédagogiques, inscriptions et apprentissage | `IMPLEMENTED` — fondation Training COURSE + Backoffice |
| `PaymentContext` | offres de formation, paiements et intégration des fournisseurs | `IMPLEMENTED` — KkiaPay + Fake de test, PAY-003 livré |
| `ContributionContext` | cotisations, situations et reçus après découverte métier | `DISCOVERY` |
| `MediaContext` | images publiques et fichiers documentaires privés minimaux | `IMPLEMENTED` — capacités CNT-004/CNT-005 |
| `AuditContext` | audit métier transverse si les besoins dépassent `LogContext` | `DISCOVERY` |

Ces contextes ne sont pas à créer dans le cadre de DOC-001.

## 4. Content domain

Le domaine Content regroupe désormais une première verticale Backoffice de
gestion des actualités. Les contenus éditoriaux publiés sur le Frontoffice
pourront ensuite inclure :

- actualités ;
- événements ;
- vidéos éditoriales ;
- galeries photos ;
- documents ;
- annonces.

Le modèle pourra partager des valeurs comme le titre, le slug, le résumé, le
statut de publication et la visibilité, tout en conservant les détails propres
à chaque type.

Le contenu éditorial n’est pas le contenu pédagogique :

```text
Content editorial != Learning content
```

Une vidéo publiée dans Content ne devient donc pas automatiquement une
ressource de formation, et une vidéo pédagogique ne doit pas être administrée
comme une actualité simplement parce que les deux utilisent YouTube.

### CNT-001 — News livré

`News` est un modèle éditorial administré sous `/admin/content/news`. Il porte
son titre, slug, chapeau, corps, statut, catégories et tags, ainsi que ses dates de publication. Les statuts
livrés sont `DRAFT`, `PUBLISHED` et `ARCHIVED` ; la publication et l’archivage
sont des transitions explicites. Le slug est régénéré uniquement tant que
l’actualité est un brouillon. `NewsCategory` et `Tag` appartiennent à
`ContentContext`, sont reliés à `News` par des associations plusieurs-à-plusieurs
et sont administrés sous `/admin/content/news-categories` et `/admin/content/tags`.
Cette verticale ne livre volontairement aucune page Frontoffice, API ni image de couverture.

### CNT-002 — Event livré

`Event` est le modèle éditorial des événements administrés sous
`/admin/content/events`. Il porte un format `IN_PERSON`, `ONLINE` ou `HYBRID`,
des dates, les informations de lieu ou de participation en ligne, un statut
`DRAFT`, `PUBLISHED`, `CANCELLED` ou `ARCHIVED`, ainsi que des catégories
propres `EventCategory` et les tags génériques `Tag`. Les transitions de
publication, annulation et archivage sont explicites ; aucun archivage
automatique, Frontoffice, inscription ou lien avec `Training LIVE` n’est livré.

## 5. Learning domain

`Training` est le concept central du e-learning.

### LRN-001 — Training COURSE livré

La première verticale de `LearningContext` administre les formations de type
`COURSE` dans le Backoffice sous `/admin/learning/trainings`. Un Training porte
son titre, slug, résumé, description riche, visibilité (`PUBLIC` ou `MEMBER`),
type d’accès (`FREE`, `PAID` ou `RESTRICTED`), statut (`DRAFT`, `PUBLISHED` ou
`ARCHIVED`), dates et couverture média publique optionnelle. Une création
commence en `DRAFT`; publier et archiver sont des transitions explicites.

La publication exige un titre, un résumé et une description non vides. Le slug
peut évoluer en brouillon et devient stable après publication. La structure
COURSE est détaillée dans LRN-002 ci-dessous ; les inscriptions, paiements et
surfaces Frontoffice/API restent hors périmètre de la tranche LRN-001.
La tranche LRN-004 ajoute désormais l’inscription et l’accès protégé au
contenu pédagogique.

Les couvertures utilisent la capacité image publique minimale de
`MediaContext`, sous la clé `training/covers`, sans relation Doctrine entre les
contextes. La suppression d’un Training ne supprime pas silencieusement son
fichier média.

Les deux types validés pour la cible sont :

```text
COURSE
LIVE
```

Un `LIVE` est une formation autonome. Il ne constitue pas automatiquement un
module d’une formation `COURSE`.

Le type est immuable après création, y compris lors du remapping Doctrine.
Les flux Backoffice Learning et le catalogue public Frontoffice sont livrés.
La consommation apprenante reste protégée dans l’espace membre ; les parcours
publics d’inscription, de paiement et de découverte apprenante restent différés.

Les concepts cibles sont :

- `Training` : identité, titre, type, statut, visibilité, accès et politique
  éventuelle de certificat ;
- `Module` : regroupement pédagogique d’une `COURSE` ;
- `LearningContent` : contenu consommable d’un module ;
- `Enrollment` : inscription et droit d’accès d’un utilisateur à un Training ;
- `LearningProgress` : progression pédagogique ;
- `Quiz` : évaluation éventuelle ;
- `Certificate` : certificat émis selon une politique validée ;
- `LiveTrainingDetails` : horaires, mode, lieu et lien HTTPS d’un `LIVE`.

`Module` est donc rattaché au parcours d’une `COURSE`, tandis que `LIVE` porte
ses propres détails de session.

### LRN-005 — Training LIVE livré

`TrainingType::LIVE` est administrable sans créer d’aggregate `LiveTraining`.
Un `Training` LIVE possède au plus un `LiveTrainingDetails`, stocké dans la
table `live_training_details` avec une unicité sur `training_id`. Les détails
valident les dates, le mode `ONLINE`/`IN_PERSON`/`HYBRID`, le lieu requis selon
le mode et les liens de connexion HTTPS.

La publication est type-aware : une COURSE applique sa readiness modules/leçons,
tandis qu’un LIVE exige seulement ses détails valides. Les deux types
réutilisent `TrainingVisibility`, `TrainingAccessType`, `TrainingCategory`,
`TrainingTag`, `Enrollment` et `TrainingAccessPolicy`. Le join membre résout
le Training par UUID et n’expose le lien qu’après contrôle d’accès serveur.

Les valeurs HTML du mode LIVE restent alignées sur l’enum métier
`ONLINE`/`IN_PERSON`/`HYBRID`. Les clés étrangères internes Learning sont
restrictives et aucune relation de persistence n’est ajoutée vers Identity,
Media ou StoredFile.

### LRN-002 — Structure COURSE : modules et leçons

Le Backoffice expose désormais `CourseModule` et `Lesson` uniquement pour les
formations `COURSE` :

    Training
      └── CourseModule (trainingId, position)
            └── Lesson (moduleId, position)

Les modules et les leçons sont des modèles autonomes, référencés par
identifiants scalaires. `Training` ne charge donc pas tout l’arbre comme un
agrégat géant ; l’écran Programme l’assemble par Query dédiée. Les positions
sont persistées et normalisées de `1` à `N` après création, suppression ou
réordonnancement.

Une `COURSE` ne peut être publiée que si elle possède au moins un module et que
chaque module contient au moins une leçon. Une structure publiée reste
modifiable sans versioning dans cette tranche. La suppression explicite d’un
module supprime ses leçons ; la suppression d’une leçon réordonne les leçons
restantes. Le déplacement d’une leçon vers un autre module est différé.

Les contenus de leçon, médias pédagogiques, durée, progression, quiz,
certificats et lecteur restent hors périmètre de LRN-002.

### LRN-003 — Contenu pédagogique des leçons

Une `Lesson` peut porter un contenu HTML issu du rich-text editor partagé, une
référence vidéo YouTube validée (`watch`, `youtu.be` ou `embed`) et des
`LessonResource`. Les iframes et scripts ne sont pas conservés dans le contenu.
Les ressources réutilisent `MediaContext.StoredFile` par identifiant scalaire,
mais leurs fichiers sont stockés séparément sous la racine privée
`private/learning/resources`. Cette capacité reste Backoffice-only jusqu’à la
livraison de l’accès pédagogique.

Une leçon est prête pour publication si elle possède un contenu significatif,
une vidéo YouTube valide ou au moins une ressource. Une `COURSE` publiée reste
éditable sans versioning. Les ressources ont un titre, un ordre persistant et
un téléchargement sécurisé réservé au Backoffice ; leur suppression détache
la ressource puis nettoie le fichier uniquement s’il n’est plus référencé.

### LRN-004 — Enrollment et contrôle d’accès

`Enrollment` conserve uniquement `trainingId` et `userId` scalaires, avec une
contrainte d’unicité par couple. Les statuts sont `ACTIVE` et `REVOKED`, et la
source est `SELF_SERVICE` ou `ADMIN_GRANT`. Une révocation conserve la ligne et
une réactivation réutilise la même inscription.

La `TrainingAccessPolicy` est la source de vérité : le contenu exige un
utilisateur actif, une formation `PUBLISHED` et une inscription `ACTIVE`.
`PUBLIC` décrit la visibilité de la fiche et ne donne aucun accès anonyme ;
`FREE` autorise l’auto-inscription, tandis que `PAID` et `RESTRICTED` restent
indisponibles sans flux métier ultérieur. Les téléchargements membre résolvent
la ressource jusqu’à sa formation avant d’appliquer cette policy, ce qui
empêche l’IDOR.

### LRN-004A — Catégories et tags des formations

`TrainingCategory` et `TrainingTag` sont deux taxonomies propres à
`LearningContext`. Elles restent plates, facultatives et servent uniquement à
la classification, à la découverte future et au filtrage du Backoffice. Elles
ne sont ni des permissions ni un mécanisme d’autorisation et ne réutilisent
pas `ContentContext\Tag`.

Les associations sont ManyToMany au niveau persistence entre `Training` et
chaque taxonomie. Les slugs sont uniques dans leur propre type et sont
regénérés lors d’un renommage tant qu’aucune URL publique Learning n’existe.
Une catégorie ou un tag utilisé par une formation ne peut pas être supprimé.

### LRN-006 — Progression apprenant

L'audience apprenante actuelle est limitée à un utilisateur actif portant le
rôle explicite `ROLE_AVOCAT`. `ROLE_ADMIN` et `ROLE_SUPER_ADMIN` conservent
leurs capacités Backoffice mais ne deviennent pas automatiquement apprenants.
Cette éligibilité est centralisée par `TrainingLearnerEligibility` et
réutilisée par `TrainingAccessPolicy`, les inscriptions et l'achat des
formations payantes. Elle ne supprime pas les `Enrollment` historiques d'un
utilisateur qui perd ensuite son éligibilité.

`LessonProgress` représente la progression d’un utilisateur inscrit dans une
`COURSE`. Son identité métier est le couple `enrollmentId`/`lessonId`; les
états sont `IN_PROGRESS` et `COMPLETED`, tandis qu’une progression absente
représente l’état initial non commencé. `startedAt`, `lastAccessedAt` et
`completedAt` sont pilotés par les commandes membre idempotentes et restent
indépendants du statut `Enrollment`.

Le contrôle d’accès exige une `COURSE` publiée et une inscription active. La
révocation ne supprime pas les lignes; les opérations sont simplement refusées
jusqu’à réactivation. Une leçon ou un module ayant une progression ne peut pas
être supprimé. Le résumé est calculé à la lecture et n’introduit aucun statut
`Enrollment::COMPLETED`.

## 6. Visibilité et accès Learning

La visibilité et l’accès sont indépendants :

```text
TrainingVisibility = PUBLIC
TrainingAccessType = PAID
```

signifie que la fiche est visible dans le catalogue public, mais que le
contenu pédagogique est réservé aux utilisateurs autorisés. `Enrollment` et
`TrainingAccessPolicy` contrôlent la consommation effective.

La confidentialité YouTube ou le fait qu’un lien soit partagé ne remplacent
jamais l’autorisation applicative.

## 7. Contribution domain

Le domaine Contribution reste en `DISCOVERY`. Aucun modèle définitif ne doit
être figé avant validation du fonctionnement existant et des règles de
gouvernance.

Les questions à résoudre couvrent notamment :

- périodes et exercice de rattachement ;
- types de cotisations ;
- mode de détermination des montants ;
- exemptions, remises et pénalités ;
- paiements partiels et arriérés ;
- régularisations et corrections ;
- reçus et numérotation ;
- source de vérité actuelle ;
- import, synchronisation ou remplacement ;
- audit des opérations sensibles.

## 8. Payment domain

`PaymentContext` est un contexte distinct des règles Learning et Contribution.
La fondation PAY-001 porte les offres de formation, les paiements et un
provider Fake de test. PAY-002/PAY-002B ajoutent KkiaPay et le SDK PHP officiel
derrière les ports de paiement, avec vérification serveur et webhook Symfony.

```text
Payment
TrainingOffer
Fake provider
```

Les règles livrées sont :

- le montant et la devise sont copiés dans Payment au moment de l’initiation ;
- la devise d’une TrainingOffer est choisie dans le référentiel currencies
  actif d’AdminContext ; Payment ne dépend pas de son entité Doctrine et
  snapshotte uniquement son code ISO ;
- l’éligibilité est vérifiée côté serveur (PUBLISHED + PAID, offre active,
  compte actif, absence d’accès actif) ;
- l’initiation est idempotente par utilisateur et clé d’idempotence ;
- les transitions financières sont PENDING -> CONFIRMED ou PENDING -> FAILED ;
- un paiement confirmé porte séparément un fulfillment nullable puis
  PENDING ; l’activation Learning réussie le fait passer à COMPLETED sans
  modifier le statut financier ;
- la confirmation validée persiste le fait financier avant de transmettre
  l’accès à Learning via un port applicatif ; une erreur Learning laisse
  CONFIRMED/PENDING et reste retryable ;
- le fulfillment crée ou réutilise une inscription ACTIVE de source PAYMENT ;
- Payment ne possède aucune relation Doctrine vers Identity ou Learning ;
  seuls userId, trainingId et l’offre interne sont stockés ;
- aucun fournisseur réel n’est requis pour les tests : Fake reste disponible.

PAY-002 ajoute KkiaPay uniquement comme adaptateur Infrastructure. Le
PaymentContext reste provider-agnostic : l’initiation crée un paiement
PENDING avec un UUID local utilisé comme partnerId, tandis que la
référence KkiaPay n’est renseignée qu’après vérification serveur. Un callback
JavaScript ou un webhook isolé ne confirme jamais un paiement. Le webhook
Symfony valide le secret, puis le verifier KkiaPay contrôle la transaction,
le montant, le partnerId et, lorsque fourni de manière fiable, la devise
avant de dispatcher ConfirmPaymentCommand ou FailPaymentCommand.

Le traitement des événements webhook est synchrone dans cette fondation :
le transport Messenger asynchrone existe dans le projet, mais aucun worker
supervisé n’est présupposé par PAY-002. Les doublons restent idempotents et
un événement FAILED ne rétrograde jamais un paiement CONFIRMED.

Le Backoffice expose la gestion des tarifs et la consultation en lecture seule
des paiements. Le parcours membre expose l’initiation POST protégée par
authentification et CSRF, avec checkout KkiaPay minimal côté navigateur ; les
callbacks navigateur ne constituent jamais une preuve de paiement.

PAY-002B remplace l’appel HTTP manuel de vérification par le SDK officiel
`kkiapay/kkiapay-php`. Le SDK reste strictement dans l’Infrastructure,
encapsulé par `KkiaPaySdkClient`; sa réponse est transformée en DTO interne
avant d’atteindre le consumer et les commandes Payment. Les erreurs techniques
ou les statuts non terminaux laissent le paiement rejouable en `PENDING`.

PAY-003 ajoute `PaymentFulfillmentStatus` (`PENDING`,
`COMPLETED`), les dates et compteurs de tentative nécessaires à
l’exploitation. Les anciennes lignes `CONFIRMED` restent nullable lors de
la migration : elles sont diagnostiquées et réconciliées sans supposer
l’existence d’un `Enrollment`. La commande
`app:payment:reconcile-fulfillment` rejoue uniquement le traitement
interne pour les paiements confirmés et ne rappelle jamais le provider.

Les listings Payment et TrainingOffer utilisent le port batch
`TrainingCatalogInterface::getByIds()`. Ils ne chargent pas un Training par
ligne et tolèrent un Training historique devenu indisponible avec le libellé
`Formation indisponible`. Ce port retourne uniquement des références de lecture
et ne crée aucune relation Doctrine cross-context.

## 9. Services transverses

Identity, Auth, Notification, Log, Shared et les services média éventuels sont
des capacités transverses. Ils ne doivent pas absorber les règles des domaines
métier.

Le mécanisme événementiel reste synchrone. `EventEmitterFeature` tamponne
temporairement des objets scalaires sur les agrégats, puis
`SharedContext\Domain\Service\EventDispatcher\EventDispatcher` les transmet au
dispatcher Symfony dans l'ordre ; `releaseEvents()` vide le tampon après
lecture. Les événements Learning et Payment sont consommés séparément par
`NotificationSubscriber` pour les notifications et par les subscribers
`LogContext` pour l'audit métier. Content publie ses faits de cycle de vie via
`ContentLifecycleEvent`; Learning publie les transitions Training et réutilise
les événements Enrollment existants; Payment publie la confirmation et réutilise
son événement d'échec; Identity réutilise les faits de rôle et de mot de passe.
Les payloads restent scalaires et les producteurs n'importent pas `LogContext`.

Le commit métier précède le dispatch : les consommateurs sont best-effort et
journalisent leurs erreurs. Une clé de déduplication SHA-256, protégée par une
contrainte unique en base, rend les replays sans effet. La livraison n'est pas
encore garantie par Outbox.

La lecture globale des notifications publiques n'est pas redéfinie ici : la
dette liée à leur état lu/non lu et à l'action « tout marquer comme lu » reste
à traiter séparément.

Le paiement est transverse par ses intégrations, mais conserve sa frontière
propre lorsqu’il sera introduit.

## 10. Direction des dépendances

La direction reste :

```text
Presenter -> Application -> Domain
Infrastructure -> Domain
```

Le Domain ne dépend pas de Symfony, Doctrine, Twig, HTTP ou Infrastructure.
Les écritures passent par des Commands et les lectures par des Queries, avec
un niveau de CQRS pragmatique adapté à la taille du cas d’usage.

## 11. Décisions reportées

Les éléments suivants restent volontairement ouverts :

- frontière exacte de `MediaContext` ;
- nécessité d’un `AuditContext` distinct de `LogContext` ;
- contrat d’audit métier, distinct des `Logs` techniques actuels ;
- garantie de livraison, rétention et éventuelle Outbox pour l’audit métier ;
- politique de rétention et garanties transactionnelles de l’audit métier ;
- modèle définitif des cotisations ;
- certification réelle Sandbox KkiaPay ;
- provisionnement de la racine persistante de stockage selon l’environnement ;
- détails de certification, quiz et replay ;
- contrat API mobile.

## CNT-004 — Galeries photos et Media minimal

`ContentContext` possède désormais `PhotoGallery` et `PhotoGalleryItem`.
Une galerie est `DRAFT`, `PUBLISHED` ou `ARCHIVED`; elle ne peut être publiée
que si elle contient des images, une couverture appartenant à la galerie et un
texte alternatif non vide pour chaque image. Le slug reste modifiable en
brouillon et devient stable après publication. Les tags génériques existants
sont réutilisés.

`MediaContext` est une capacité technique minimale, limitée aux images
publiques JPEG, PNG et WebP. `Media` ne référence jamais une galerie ou un
autre consommateur métier : `PhotoGalleryItem` conserve uniquement son
`mediaId`. Une galerie supprimée détache ses items, mais ne détruit pas
automatiquement les médias; leur suppression physique est explicite et refusée
tant qu’un consommateur les référence.

Le stockage persistant est centralisé par `APP_STORAGE_DIR`, dont la valeur
canonique de déploiement est `/shared/storage` : les images publiques résident sous
`public/galleries` et `public/content/covers`, tandis que les documents privés
résident sous `private/documents`, sans chemin absolu ni relation de
consommateur enregistrée en base. `var/` reste réservé au runtime Symfony et
aux caches.

## CNT-004A — Couvertures et galeries liées

`News` et `Event` peuvent chacun référencer au plus une couverture `Media` et
au plus une `PhotoGallery` via des identifiants scalaires. La couverture est
une image publique uploadée directement depuis le formulaire Backoffice et
stockée sous `public/content/covers`; aucune entité Doctrine `Media` n’est
référencée par `ContentContext`. Une galerie reste un agrégat Content
indépendant et sa suppression est refusée tant qu’une actualité ou un
événement la référence.

## CNT-003 — EditorialVideo livré

`EditorialVideo` est le modèle de référence des vidéos éditoriales administrées
sous `/admin/content/videos` et publiées sous `/videos`. Il est distinct de
`Training` et porte un titre, un slug, un résumé, une description Tiptap
nettoyée, un fournisseur (`YOUTUBE` ou `EXTERNAL_URL`), une URL externe, un
statut `DRAFT`, `PUBLISHED` ou `ARCHIVED`, ses dates et les tags génériques
`Tag`. La publication et l’archivage sont des transitions explicites ; une
vidéo YouTube n’est intégrée qu’à partir d’un identifiant extrait d’une URL
validée. Le Frontoffice expose uniquement les vidéos `PUBLISHED` possédant une
date de publication ; les galeries publiques restent hors de cette livraison.

### CNT-007 — Catégories éditoriales des vidéos

`EditorialVideoCategory` est une taxonomie Content dédiée aux vidéos
éditoriales. Une vidéo possède au plus une catégorie via une référence Doctrine
interne au même contexte ; la colonne reste nullable pour préserver les
vidéos historiques, mais toute nouvelle création ou modification Backoffice
doit sélectionner une catégorie. La suppression d’une catégorie utilisée est
refusée. Le listing Backoffice expose un filtre et le listing public accepte
le filtre optionnel `category` par slug sans modifier le rendu public.

## CNT-006 — Pages statiques

`Page` est un contenu institutionnel simple de `ContentContext`. Une page
commence en `DRAFT` et passe explicitement à `PUBLISHED` via `publish()` ; elle
peut revenir à `DRAFT` via `unpublish()`. La date `publishedAt` est renseignée
à la première publication et conservée lors d’une republication.

Le slug est normalisé et unique, mais reste éditable depuis le Backoffice. Le
contenu est saisi avec le composant Tiptap existant et nettoyé par le sanitizer
serveur. CNT-006 livre la gestion Backoffice, le listing paginé, les actions
individuelles et bulk de suppression, ainsi que la lecture publique d’une Page
publiée via `/informations/{slug}`. Depuis CNT-008, les Pages `BAR` sont
exposées canoniquement sous `/le-barreau/{slug}` et l’ancienne URL
`/informations/{slug}` redirige définitivement vers cette route. Le hub public
`/le-barreau` liste les Pages `BAR` publiées dans leur ordre éditorial. Il
n’existe pas de listing Frontoffice générique des autres groupes ; les Pages
publiées d’un même groupe peuvent afficher une sidebar contextuelle utilisant
les URLs canoniques.

### CNT-006A — Couverture facultative des pages

Une `Page` peut référencer une couverture image publique via le seul identifiant
scalaire nullable `coverMediaId`. La couverture n'est jamais requise pour créer
ou publier une page. Le formulaire Backoffice réutilise le composant de cover
existant pour l'upload, l'aperçu, le remplacement et le retrait ; les fichiers
sont stockés sous `public/content/covers` avec les validations et le nom serveur
du `MediaContext`. `Media` ne porte aucune relation Doctrine vers `Page`.

Le vérificateur d'usage Content bloque la suppression physique d'un média encore
référencé par une page, une actualité, un événement ou une galerie. Le retrait et
le remplacement d'une cover tentent le nettoyage du média devenu orphelin sans
modifier les audits Page existants. La query `FindPublishedPageBySlug` restitue
`coverMediaId` lorsque la page publiée en possède une.

## CNT-009 — Donnée structurée du Bâtonnier

`BatonnierMandate` appartient à `ContentContext` et conserve l'historique des
mandats du Bâtonnier : nom complet, dates de mandat, présentation courte et
référence scalaire nullable vers un portrait `Media`. Un mandat dont la date de
fin est nulle est courant ; la base garantit qu'il n'y en a jamais plus d'un,
tandis que l'absence de mandat courant reste valide.

Le portrait est une image publique gérée par `MediaContext` sous le préfixe
`institution/portraits`, sans relation Doctrine cross-context. Le média reste
protégé tant qu'il est référencé par un mandat. Les mandats historiques ne sont
pas supprimés en bulk et aucune donnée officielle n'est ajoutée par fixture.

Le Backoffice expose la liste, le détail, la création et la modification sous
`/admin/content/batonnier`, avec `HasGroupAccess(RoleGroupEnum::BATONNIER)` et
les permissions `BATONNIER_LIST`, `BATONNIER_VIEW`, `BATONNIER_CREATE` et
`BATONNIER_EDIT`. La Page BAR `le-batonnier` reste propriétaire du contenu riche
public ; lorsqu'un mandat courant existe, elle affiche en plus le bloc structuré
du Bâtonnier. Sans mandat courant, la page reste accessible sans bloc artificiel.

## CNT-010 — Conseil de l’Ordre structuré

`CouncilMember` appartient à `ContentContext` et conserve une composition
historique du Conseil de l’Ordre : nom complet, fonction textuelle, ordre
d’affichage, dates facultatives et référence scalaire nullable vers un portrait
`Media`. Une date de fin nulle signifie que le membre est courant ; plusieurs
membres courants sont autorisés. La page BAR `conseil-de-l-ordre` reste
propriétaire du contenu riche et affiche uniquement les membres courants,
triés par ordre puis par nom. Sans membre courant, la page reste accessible sans
section vide.

Le Backoffice expose la liste, le détail, la création et la modification sous
`/admin/content/conseil-ordre`. Il n’y a pas de suppression dans cette première
version afin de préserver l’historique. Les portraits réutilisent le stockage
public `institution/portraits` et le vérificateur composite bloque la suppression
d’un média encore utilisé.

## 12. Références

- [`docs/ARCHITECTURE.md`](ARCHITECTURE.md) ;
- [`docs/PERMISSIONS.md`](PERMISSIONS.md) ;
- [`docs/specifications_techniques_plateforme_avocats_ci_v3.md`](specifications_techniques_plateforme_avocats_ci_v3.md).

## CNT-005 — Publications documentaires sécurisées

`DocumentPublication` appartient à `ContentContext` et porte le titre, le slug,
la description, les tags, le statut éditorial et le niveau d’accès. Le statut
(`DRAFT`, `PUBLISHED`, `ARCHIVED`) est séparé de `DocumentAccessLevel`
(`PUBLIC`, `MEMBER`, `LAWYER`, `RESTRICTED`, `PRIVATE`). `LAWYER` requiert un
compte activé portant explicitement `ROLE_AVOCAT` ; cette règle ne constitue
pas une preuve de statut ordinal juridiquement actif. `StoredFile` appartient à
`MediaContext`, ne connaît aucun consommateur métier et stocke les documents
hors de `public/` avec une clé relative (`documents/<nom>`) et une empreinte
SHA-256. Cette
capacité limitée aux PDF, DOCX, XLSX et PPTX n’est ni une médiathèque, ni un
DAM, ni le stockage Learning privé.

La rubrique membre du Fonds de Solidarité réutilise ces publications et les
tags génériques Content : seules les publications `PUBLISHED`, `LAWYER` portant
le slug de tag configuré `content.fund_solidarity_tag_slug` (`fonds-de-solidarite`
par défaut) y apparaissent. Cette classification éditoriale ne change ni les
règles d’accès LAWYER ni le contrôle serveur au téléchargement.

## DIR-001 — Fondation de l’annuaire Avocats & Cabinets

`LawyerProfile` possède son propre UUID public, distinct de l’identifiant
séquentiel et de l’UUID `User`. La publication reste opt-in (`directoryVisible`
false par défaut). Les coordonnées publiques sont `professionalEmail` et
`professionalPhone` ; l’email du compte et les coordonnées personnelles ne sont
pas des sources de données publiques. Le portrait est une référence scalaire
nullable `portraitMediaId` vers `MediaContext`, stockée dans
`institution/lawyers` et protégée contre la suppression tant qu’elle est
référencée.

La règle de publication V1 exige simultanément le consentement de visibilité,
un compte activé portant explicitement `ROLE_AVOCAT` et un statut professionnel
différent de `SUSPENDED`. C’est un critère applicatif de publication, pas une
preuve du statut ordinal officiel. `ProfessionalStatus` reste inchangé. Un
Cabinet est publiable uniquement si `directoryVisible` est vrai et son statut
est `ACTIVE`. La visibilité du Cabinet ne rend jamais ses avocats publics ;
chaque fiche doit satisfaire sa règle propre. Les profils sans Cabinet restent
valides.

DIR-001 ne crée encore ni route de listing/détail publique ni import réel de
l’ancien annuaire. Les fixtures ajoutées sont synthétiques et servent aux
tests de visibilité, d’éligibilité et de protection des portraits.

### DIR-002 — Profils annuaire sans compte

`LawyerProfile` porte désormais son identité publique dans `displayName`,
indépendamment de `User.name`. Un profil peut être lié à un compte (`User`)
ou exister sans compte, notamment pour représenter une fiche professionnelle
historique. La relation utilisateur est nullable et sa suppression physique
préserve la fiche (`ON DELETE SET NULL`). La recherche et les fiches publiques
utilisent uniquement `displayName`; le parcours membre, lui, continue à
retrouver son profil exclusivement par l’utilisateur authentifié. Aucun
rattachement automatique ni flux de revendication n’est créé.

La publication d’un profil lié à un compte requiert toujours `directoryVisible`,
un compte activé, le rôle explicite `ROLE_AVOCAT` et un statut différent de
`SUSPENDED`. Pour un profil sans compte, visibilité explicite et statut différent
de `SUSPENDED` suffisent. `UNKNOWN` représente un statut professionnel non
vérifié : il ne signifie pas « Actif », n’est pas attribué rétroactivement aux
profils existants et devient le défaut des nouveaux profils.

`legacySourceUuid` nullable et unique sur LawyerProfile et Cabinet conserve
uniquement la provenance d’un futur import ; l’UUID Avocat CI reste l’identité
publique. La provenance n’est exposée ni dans les read models publics, ni dans
les URLs ou interfaces publiques. Les cabinets conservent leurs téléphones dans
une liste ordonnée ; la migration convertit le téléphone historique unique en
liste sans perte. Le formulaire Backoffice accepte un numéro par ligne et la
fiche publique les affiche séparément. Ce changement de modèle ne réalise aucun
import de l’ancien annuaire.

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
| `LogContext` | logs applicatifs et activité selon l’implémentation actuelle |
| `NotificationContext` | mécanismes de notification existants |
| `WebContext` | shell et capacités du site public existant |

`AdminContext` n’est pas le propriétaire automatique de toutes les fonctions
accessibles sous `/admin`. Une fonction métier future reste dans le contexte qui
porte sa règle.

## 3. Contextes métier cibles

| Contexte candidat | Responsabilité cible | Statut |
|---|---|---|
| `ContentContext` | contenus éditoriaux publics et leur administration | `IMPLEMENTED` — première verticale News |
| `LearningContext` | formations, contenus pédagogiques, inscriptions et apprentissage | `IMPLEMENTED` — fondation Training COURSE + Backoffice |
| `PaymentContext` | commandes, transactions et intégration des paiements | `PLANNED` |
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

`PaymentContext` est un contexte cible distinct des règles Learning et
Contribution. Il pourra porter :

```text
Order
Payment
PaymentTransaction
Provider
Webhook
```

Les principes déjà décidés sont :

- validation côté serveur ;
- idempotence des webhooks et confirmations ;
- traçabilité des transactions ;
- aucun accès accordé sur la seule réponse du frontend ;
- aucun choix de fournisseur avant validation du besoin ;
- `LearningContext` décide de l’activation d’une inscription ;
- `PaymentContext` ne manipule pas directement la persistence de Learning ou
  de Contribution.

## 9. Services transverses

Identity, Auth, Notification, Log, Shared et les services média éventuels sont
des capacités transverses. Ils ne doivent pas absorber les règles des domaines
métier.

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
- modèle définitif des cotisations ;
- fournisseur de paiement ;
- stratégie de stockage des documents privés ;
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
canonique est `/shared/storage` : les images publiques résident sous
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
sous `/admin/content/videos`. Il est distinct de `Training` et porte un titre,
un slug, un résumé, une description Tiptap nettoyée, un fournisseur (`YOUTUBE`
ou `EXTERNAL_URL`), une URL externe, un statut `DRAFT`, `PUBLISHED` ou
`ARCHIVED`, ses dates et les tags génériques `Tag`. La publication et
l’archivage sont des transitions explicites ; une vidéo YouTube n’est intégrée
qu’à partir d’un identifiant extrait d’une URL validée. Aucun upload, appel API
YouTube, live ou écran Frontoffice n’est livré.

## 12. Références

- [`docs/ARCHITECTURE.md`](ARCHITECTURE.md) ;
- [`docs/PERMISSIONS.md`](PERMISSIONS.md) ;
- [`docs/specifications_techniques_plateforme_avocats_ci_v3.md`](specifications_techniques_plateforme_avocats_ci_v3.md).

## CNT-005 — Publications documentaires sécurisées

`DocumentPublication` appartient à `ContentContext` et porte le titre, le slug,
la description, les tags, le statut éditorial et le niveau d’accès. Le statut
(`DRAFT`, `PUBLISHED`, `ARCHIVED`) est séparé de `DocumentAccessLevel`
(`PUBLIC`, `MEMBER`, `RESTRICTED`, `PRIVATE`). `StoredFile` appartient à
`MediaContext`, ne connaît aucun consommateur métier et stocke les documents
hors de `public/` avec une clé relative (`documents/<nom>`) et une empreinte
SHA-256. Cette
capacité limitée aux PDF, DOCX, XLSX et PPTX n’est ni une médiathèque, ni un
DAM, ni le stockage Learning privé.

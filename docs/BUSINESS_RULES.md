# Avocat CI — Règles métier

## 1. Statut des règles

Ce document distingue les règles validées des sujets encore ouverts. Une règle
non confirmée apparaît dans `Business decisions pending` et ne doit pas être
implémentée par déduction.

## 2. Identity et permissions — règles validées

Le catalogue de rôles actif est limité à :

```text
ROLE_SUPER_ADMIN
ROLE_ADMIN
ROLE_AVOCAT
ROLE_USER
```

Les permissions configurables et persistées constituent l’autorité lorsqu’une
configuration existe. Les valeurs par défaut ne servent que lorsqu’aucune
configuration persistée n’est disponible. Une configuration persistée vide
reste donc une révocation effective.

La hiérarchie actuelle est :

```text
ROLE_AVOCAT      -> ROLE_USER
ROLE_ADMIN       -> ROLE_AVOCAT
ROLE_SUPER_ADMIN -> ROLE_ADMIN
```

Les décisions d’autorisation sont toujours vérifiées côté serveur. Masquer une
action dans Twig ne constitue pas une autorisation.

## 3. Surfaces et sécurité — règles validées

### Public Frontoffice

Les routes publiques sont explicitement déclarées. Une ressource publique peut
être consultée sans connexion selon sa visibilité.

### Member Area

`/espace` est une surface authentifiée. Les futurs services membres doivent
réutiliser l’utilisateur et les mécanismes d’autorisation existants.

### Backoffice

`/admin` est la surface administrative. Le dashboard est réservé aux rôles
administratifs autorisés par la configuration actuelle ; les écrans métier
futurs appliqueront en plus les permissions de leur contexte propriétaire.

## 4. Content — règles validées

- un contenu éditorial n’est pas automatiquement un contenu pédagogique ;
- une publication doit respecter son statut et sa visibilité ;
- un document ou média protégé doit faire l’objet d’un contrôle serveur ;
- la visibilité publique d’une fiche ne vaut pas accès à une ressource privée.

Pour `CNT-001`, les règles suivantes sont implémentées :

- une nouvelle actualité est créée en `DRAFT` ;
- seul un brouillon peut passer à `PUBLISHED` ; la première publication fixe
  `publishedAt` ;
- seule une actualité publiée peut passer à `ARCHIVED` ;
- une actualité publiée ou archivée conserve son slug lors d’une modification ;
- le slug est unique ;
- la suppression est une action explicite protégée par permission et
  confirmation, sans suppression en cascade d’un historique externe.

Le champ de statut n’est pas éditable dans le formulaire : les transitions
passent par les commandes applicatives dédiées.

Pour `CNT-001A`, les catégories d’actualités et les tags génériques sont
portés par `ContentContext`. Leur slug est normalisé et unique. Une actualité
peut recevoir plusieurs catégories et plusieurs tags ; la sélection est
remplacée via le use case de création ou de modification. Une catégorie ou un
tag utilisé par au moins une actualité ne peut pas être supprimé : l’opération
est refusée avec un message explicite, sans détachement silencieux.

### CNT-002 — Événements éditoriaux

- un nouvel événement commence en `DRAFT` ;
- `EventFormat` vaut `IN_PERSON`, `ONLINE` ou `HYBRID` ;
- `startsAt` est obligatoire et `endsAt`, lorsqu’elle est renseignée, ne peut
  pas précéder le début ;
- les formats présentiel et hybride nécessitent un nom de lieu et une adresse
  au moment de la publication ; les formats en ligne et hybride nécessitent
  une URL `http` ou `https` sûre ;
- seul un brouillon peut être publié ; la première publication fixe
  `publishedAt` ; seul un événement publié peut être annulé ; un événement
  publié ou annulé peut être archivé ;
- l’annulation conserve les dates, le lieu et l’URL ; les événements passés ne
  changent pas automatiquement de statut ;
- le slug reste stable après publication ; une `EventCategory` utilisée par un
  événement et un `Tag` utilisé par une actualité ou un événement ne peuvent
  pas être supprimés.

Les événements éditoriaux restent distincts de `Training LIVE`. Les
inscriptions, participants, tickets, calendrier public et archivage automatique
sont hors périmètre de CNT-002.

## 5. Learning — règles validées

`Training` est le concept principal du e-learning.

Les types validés sont :

```text
COURSE
LIVE
```

Un `LIVE` est un Training autonome. Il n’est pas automatiquement un module
d’une `COURSE`.

La visibilité d’un Training et son accès sont indépendants :

```text
TrainingVisibility = PUBLIC
TrainingAccessType = PAID
```

signifie que la fiche est publiquement visible, tandis que la consommation du
contenu nécessite une autorisation serveur.

`Enrollment` contrôle l’accès pédagogique. Le fournisseur vidéo, notamment
YouTube, ne devient pas l’autorité des droits d’accès.

Pour LRN-001/LRN-002, une nouvelle `COURSE` commence en `DRAFT`. La publication
est explicite, renseigne `publishedAt` et exige un titre, un résumé, une
description non vides, au moins un module et au moins une leçon par module.
Une formation publiée peut être archivée;
les autres transitions ne sont pas exposées par le formulaire.

Le slug est modifiable en brouillon et stable après publication. Les
couvertures sont des images publiques `JPEG`, `PNG` ou `WebP` stockées par
`MediaContext`; `MediaContext` ne connaît pas `Training`, et la suppression
d’une formation ne supprime pas silencieusement un média potentiellement
partagé.

Une inscription est unique par utilisateur et formation. Elle commence active,
peut être révoquée puis réactivée sans suppression physique. L’auto-inscription
est réservée aux formations publiées `FREE`; l’attribution administrateur est
possible pour toute formation publiée. Une formation `DRAFT` ou `ARCHIVED` ne
peut pas activer une nouvelle inscription. Une formation avec des inscriptions
ne peut pas être supprimée.

Le type `COURSE`/`LIVE` est immuable après la création. Les modes LIVE sont
transportés par leurs valeurs métier stables `ONLINE`, `IN_PERSON` et `HYBRID`.
Une suppression bulk de formations prévalide toute la sélection : si une
formation possède des inscriptions, aucune formation de la sélection n’est
supprimée et un message explicite est affiché.

### LRN-004A — Classification Learning

Les catégories et tags de formation sont facultatifs, plats et indépendants
des taxonomies éditoriales Content. Ils peuvent être associés à tout `Training`,
quel que soit son type futur (`COURSE` ou `LIVE`). Ils n’influencent jamais
`Enrollment`, `TrainingAccessPolicy` ou les permissions. La suppression d’une
taxonomie utilisée est refusée ; une suppression bulk conserve les éléments
utilisés sans provoquer d’erreur serveur.

Les modules et leçons ont un ordre persistant `1..N`, normalisé après chaque
création, suppression ou réordonnancement. Un module supprimé avec confirmation
supprime ses leçons ; une leçon ou un module ayant une progression est protégé
contre la suppression. Une COURSE publiée reste éditable structurellement sans
versioning.

### LRN-005 — Training LIVE

`LIVE` reste un type autonome de `Training`, distinct de `ContentContext\Event`
et sans module ni leçon. Ses informations sont portées par un unique
`LiveTrainingDetails` : début, fin, mode, lieu optionnel et lien de connexion
optionnel. Les dates doivent respecter `startsAt < endsAt`.

Les modes `ONLINE`, `IN_PERSON` et `HYBRID` exigent respectivement un lien
HTTPS, un lieu, ou les deux. Le serveur valide l’URL ; aucun fournisseur
externe n’est appelé et Learning ne prétend pas empêcher le partage d’un lien
externe par un membre autorisé.

La publication d’un LIVE exige des détails valides, mais pas de modules ni de
leçons. COURSE conserve ses règles de readiness existantes. Le type est choisi
à la création et reste immuable. COURSE et LIVE réutilisent visibilité, accès,
catégories, tags, Enrollment et `TrainingAccessPolicy`.

Le lien de connexion n’est jamais affiché directement à partir d’une donnée
cliente. `GET /espace/learning/trainings/{uuid}/join` vérifie l’utilisateur,
le Training publié et l’Enrollment actif avant de rediriger vers le lien HTTPS
administré. Les sessions `IN_PERSON`, les brouillons, archivés et inscriptions
révoquées ne donnent pas accès à un lien.

Un téléchargement membre autorisé dont le fichier physique est absent répond
par un `404` contrôlé et journalise les identifiants de la ressource, de la
formation et du fichier ; il ne redirige pas avec une erreur générique. Une
session `IN_PERSON` répond de manière contrôlée sans redirection externe.
Le déplacement inter-module est explicitement différé ; le builder fournit le
réordonnancement intra-module et les contrôles clavier Monter/Descendre.

### LRN-006 — Progression apprenant

La progression persistée concerne uniquement les formations `COURSE`. Une
absence de ligne `LessonProgress` signifie `NOT_STARTED`; seules les valeurs
`IN_PROGRESS` et `COMPLETED` sont persistées. Une ligne est unique par couple
`Enrollment`/`Lesson` et une leçon ne peut être suivie que si elle appartient
à la `COURSE` de l’inscription.

Les commandes membre de démarrage et de complétion réutilisent
`TrainingAccessPolicy`: la formation doit être publiée et l’inscription active.
Le démarrage est idempotent et actualise `lastAccessedAt`; la complétion peut
être appelée directement, renseigne `startedAt` si nécessaire et conserve la
première valeur de `completedAt`. Il n’existe pas de remise à zéro ni de
déduction automatique depuis l’ouverture d’une vidéo ou d’une ressource.

Une révocation conserve la progression mais interdit ses mutations et l’accès
aux ressources; une réactivation la rend de nouveau accessible. Une
progression existante bloque la suppression de la leçon et de son module. Le
pourcentage est calculé à la lecture sur les leçons de la `COURSE`, arrondi à
l’entier et n’est jamais persisté. Les `LIVE` n’exposent pas de progression.

## 6. Payment — PAY-001 livré

- une formation PAID publiée doit avoir une offre active pour être payable ;
- l’offre porte un montant entier et une devise ISO issue d’une entrée active
  du référentiel currencies d’AdminContext ; un code arbitraire ou inactif
  est refusé côté serveur ;
- le formulaire Backoffice propose uniquement les devises actives du
  référentiel et n’utilise pas de liste de codes codée en dur ;
- une initiation nécessite un compte actif, une clé d’idempotence et aucun
  accès actif existant ;
- une même clé pour un utilisateur retourne le même paiement ; un autre
  paiement PENDING pour le même utilisateur et la même formation est refusé ;
- un paiement conserve son montant et sa devise snapshot ;
- les seules transitions sont PENDING -> CONFIRMED et PENDING -> FAILED, avec
  confirmation et échec idempotents ;
- une confirmation n’est acceptée qu’avec la référence fournisseur attendue ;
- seul un paiement confirmé transmet l’accès à Learning ; l’inscription ACTIVE
  est idempotente et porte la source PAYMENT ;
- aucun retour navigateur ne suffit à confirmer un paiement.

Payment conserve un code ISO scalaire : il ne possède aucune relation Doctrine
vers AdminContext. La devise est résolue lors de l’enregistrement de l’offre,
puis le code est snapshoté dans chaque Payment pour préserver l’historique,
même si le référentiel change ultérieurement.

PAY-001 utilise exclusivement un fournisseur Fake. KkiaPay, les webhooks, le
checkout réel, les remboursements et les paiements partiels restent hors scope.

## 7. Contribution — statut de découverte

Les règles de cotisation ne sont pas encore suffisamment connues pour produire
un modèle métier définitif.

Le système doit d’abord clarifier :

- les périodes ;
- les types de cotisations ;
- les montants et leur mode de calcul ;
- les exemptions, remises et pénalités ;
- les paiements partiels ;
- les arriérés ;
- les reçus ;
- les régularisations ;
- la source de vérité ;
- les imports ou synchronisations éventuels ;
- les exigences d’audit et de correction.

## 8. Business decisions pending

Les décisions suivantes restent explicitement ouvertes :

- durée d’accès à une formation ;
- conditions d’émission et de révocation d’un certificat ;
- score et règles de réussite des quiz ;
- disponibilité et protection des replays ;
- remboursement et annulation de paiement ;
- expiration d’une inscription ;
- fournisseur de paiement ;
- modèle définitif des cotisations ;
- source de vérité des données historiques ;
- stratégie de stockage des documents privés ;
- contrat API mobile et authentification associée.

## 9. Règles d’intégrité transverses

- les faits financiers et historiques ne sont pas supprimés pour simplifier une
  opération ;
- une correction financière est explicite et traçable ;
- les règles métier résident dans le Domain ou l’Application, pas dans les
  contrôleurs, formulaires ou templates ;
- les contextes ne manipulent pas directement les entités Doctrine d’un autre
  contexte ;
- une décision non validée doit rester visible comme question ouverte.

## CNT-003 — Vidéos éditoriales

- une nouvelle vidéo commence en `DRAFT` et son statut n’est pas éditable dans
  le formulaire ;
- seules les références `http` et `https` sont acceptées ; YouTube exige une
  URL `watch`, `youtu.be` ou `embed` reconnue ;
- seule une vidéo brouillon peut être publiée, ce qui fixe `publishedAt` ;
  seule une vidéo publiée peut être archivée ;
- le slug peut évoluer en brouillon et reste stable après publication ;
- un tag utilisé par une vidéo éditoriale ne peut pas être supprimé ;
- les iframes arbitraires, uploads, synchronisation API YouTube, vidéos Live et
  surfaces Learning ou Frontoffice restent hors scope.

## 10. Références

## CNT-004 — Galeries photos

- une galerie commence en `DRAFT`; seul un brouillon peut être publié et seule
  une galerie publiée peut être archivée ;
- publier exige une image, une couverture faisant partie de la galerie et un
  texte alternatif renseigné pour chaque image ;
- l’ordre des images est complet, stable et sans doublon ; la couverture est
  retirée si son image est détachée ;
- `altText` décrit l’image pour l’accessibilité et reste distinct de la
  `caption`, qui est facultative ;
- les médias sont conservés après suppression d’une galerie. Leur effacement
  physique est explicite et refusé quand un item les référence ;
- un tag utilisé par une galerie ne peut pas être supprimé.

## CNT-004A — Couvertures et galeries liées

- une actualité et un événement peuvent avoir zéro ou une couverture image et
  zéro ou une galerie photo ;
- les couvertures utilisent la validation Media image publique existante et
  sont stockées sous `public/content/covers` avec une clé relative ;
- remplacer ou retirer une couverture retire uniquement la référence Content ;
  le fichier physique est supprimé seulement si le Media n’est plus utilisé
  par une actualité, un événement ou une galerie ;
- une galerie associée reste sélectionnable quel que soit son statut lors de
  l’enregistrement du contenu ; son exposition publique future restera limitée
  aux galeries publiées ;
- une galerie référencée par News ou Event ne peut pas être supprimée et la
  suppression du contenu ne supprime jamais automatiquement la galerie.

## CNT-005 — Documents / Publications sécurisés

- une publication commence en `DRAFT` et son statut n’est pas éditable dans le formulaire ;
- seules `publish()` et `archive()` modifient le statut ; la première publication renseigne `publishedAt` ;
- une publication doit posséder un `StoredFile`, un titre et un niveau d’accès valide pour être publiée ;
- le slug évolue en brouillon et reste stable après publication ;
- `PUBLIC`, `MEMBER`, `RESTRICTED` et `PRIVATE` sont indépendants du statut ;
- un téléchargement externe exige `PUBLISHED` et un document `PRIVATE` n’est jamais téléchargeable hors Backoffice ;
- les fichiers sont validés par MIME réel, taille, extension cohérente et lisibilité ; exécutables, macros, archives, HTML/JS et formats non prévus sont refusés ;
- les documents sont stockés hors `public/` avec une clé aléatoire et une empreinte SHA-256 ;
- un fichier encore référencé ne peut pas être supprimé physiquement ; les tags génériques sont réutilisés.

- [`docs/PERMISSIONS.md`](PERMISSIONS.md) ;
- [`docs/ARCHITECTURE.md`](ARCHITECTURE.md) ;
- [`docs/specifications_techniques_plateforme_avocats_ci_v3.md`](specifications_techniques_plateforme_avocats_ci_v3.md).

## LRN-003 — Contenu pédagogique des leçons

Une leçon est publiable lorsqu’elle possède un contenu riche significatif, une
référence YouTube valide ou au moins une ressource. Chaque ressource doit avoir
un nom et un ordre unique ; PDF, DOCX, XLSX et PPTX sont les seuls formats
livrés. Les fichiers restent privés et téléchargeables uniquement depuis le
Backoffice tant que l’accès pédagogique membre n’est pas livré. Retirer une
ressource ne supprime pas un fichier partagé ; le nettoyage physique n’est
effectué que lorsqu’il n’existe plus aucune référence.

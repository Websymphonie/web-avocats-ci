# Avocat CI — Contexte produit

## 1. Identité du produit

Avocat CI est une plateforme numérique professionnelle destinée aux avocats et
aux professionnels du droit en Côte d’Ivoire.

La plateforme a vocation à réunir, dans un même produit fiable et sécurisé :

- la publication de contenus institutionnels et éditoriaux ;
- les événements, vidéos et galeries ;
- les formations et les Lives ;
- le suivi des cotisations et des paiements, après validation des règles
  métier ;
- les services réservés aux membres ;
- les capacités d’administration et de supervision nécessaires au produit.

La plateforme doit rester compréhensible, progressive et adaptée aux usages
professionnels. Elle ne doit pas transformer une hypothèse de spécification en
règle métier tant que celle-ci n’est pas validée.

## 2. Vision

La vision est de fournir un point d’accès numérique de confiance pour :

1. informer le public et valoriser l’activité de la profession ;
2. permettre aux membres de gérer progressivement leurs services personnels ;
3. structurer une offre de formation accessible en ligne ;
4. donner aux équipes habilitées des outils de gestion traçables ;
5. préparer, sans la précipiter, une API pour de futurs clients mobiles.

## 3. Utilisateurs principaux

| Utilisateur | Besoin principal | Surface privilégiée |
|---|---|---|
| Visiteur public | découvrir les contenus et l’offre de formation | Public Frontoffice |
| Membre / avocat | accéder à ses services et ressources autorisées | Member Area |
| Administrateur | administrer les fonctions qui lui sont attribuées | Backoffice |
| Super administrateur | administrer les fonctions techniques et les permissions | Backoffice |
| Utilisateur mobile futur | consommer les capacités exposées par API | API / Flutter, ultérieur |

Les rôles techniques existants sont documentés dans
[`docs/PERMISSIONS.md`](PERMISSIONS.md). Ils ne sont pas redéfinis par ce
document.

## 4. Surfaces produit

### Public Frontoffice

Le Frontoffice est accessible sans connexion sur les routes explicitement
publiques. Il est porté par `WebContext` dans l’état actuel.

La cible fonctionnelle couvre notamment :

- l’accueil ;
- les actualités ;
- les événements ;
- les vidéos éditoriales ;
- les galeries photos ;
- les documents publics ;
- le catalogue des formations ;
- les fiches de formations et de Lives ;
- les pages institutionnelles ;
- la recherche publique.

Dans l’état actuel, la page d’accueil `/` est disponible. Les autres capacités
ci-dessus restent des capacités cibles tant qu’un ticket et une implémentation
validée ne les ont pas livrées.

### Member Area

La Member Area est une surface authentifiée accessible sous `/espace`.

La cible fonctionnelle couvre notamment :

- le profil ;
- les formations et les Lives auxquels le membre est autorisé à accéder ;
- la progression ;
- les certificats ;
- les cotisations ;
- les paiements ;
- les notifications.

Dans l’état actuel, `/espace` fournit une surface minimale authentifiée. Aucun
`MemberContext` n’est créé : il s’agit d’une surface de présentation qui
réutilise `AuthContext` et `IdentityContext`.

### Backoffice

Le Backoffice est accessible sous `/admin` et repose sur les contrôleurs
existants. Son entrée et son dashboard sont actuellement réservés aux rôles
administratifs prévus par le socle.

À terme, les écrans d’administration doivent rester dans le contexte métier
propriétaire : les fonctions Content ne doivent pas être déplacées dans
`AdminContext` simplement parce que leur URL commence par `/admin`.

## 5. Domaines produit

Les trois grands domaines fonctionnels sont :

```text
Content
Contribution
Learning
```

Les services transverses comprennent notamment l’identité, l’authentification,
les permissions, les notifications, les logs, les médias et, lorsque le
besoin sera validé, le paiement.

Ces domaines cibles ne signifient pas que tous les Bounded Contexts doivent
être créés immédiatement. La règle de création est documentée dans
[`docs/DOMAIN.md`](DOMAIN.md).

## 6. Objectifs fonctionnels

- rendre l’information professionnelle claire et accessible ;
- offrir une expérience publique fiable et lisible ;
- permettre une progression contrôlée vers les services membres ;
- protéger les contenus et données sensibles côté serveur ;
- rendre les opérations administratives traçables ;
- séparer les règles éditoriales, pédagogiques, financières et de cotisation ;
- livrer des verticales complètes plutôt qu’un grand nombre de fondations
  spéculatives.

## 7. Principes fonctionnels

1. Le backend Symfony est l’autorité pour l’authentification, l’autorisation,
   les validations, les paiements, les inscriptions et les états métier.
2. Une visibilité publique ne signifie pas qu’un contenu protégé est librement
   consommable.
3. Le contenu éditorial et le contenu pédagogique sont deux responsabilités
   différentes, même lorsqu’ils utilisent le même fournisseur vidéo.
4. Les faits financiers et historiques sont conservés ; une correction ou une
   annulation doit être explicite.
5. Les règles de cotisation ne sont pas inventées à partir d’une interface ou
   d’un nom de route.
6. Les fonctionnalités futures sont livrées progressivement et documentées
   avec leur statut.

## 8. Contraintes générales

- Symfony, Twig et le CQRS pragmatique existant restent la base du produit ;
- la direction de dépendance est `Presenter -> Application -> Domain` et
  `Infrastructure -> Domain` ;
- le Domain ne dépend ni de Symfony, ni de Doctrine, ni de Twig ;
- l’interface utilisateur est en français et respecte
  [`docs/UI_UX_GUIDELINES.md`](UI_UX_GUIDELINES.md) ;
- les routes publiques et protégées sont explicitement distinguées ;
- aucune API, authentification mobile ou intégration de paiement n’est
  introduite avant qu’un besoin réel et un contrat soient validés.

## 9. Périmètre actuel

### Disponible dans le socle

- `AuthContext` pour l’authentification ;
- `IdentityContext` pour les utilisateurs et l’autorisation ;
- `AdminContext` pour l’administration technique existante ;
- `SharedContext` pour les services techniques partagés ;
- `LogContext` et `NotificationContext` — `COMPLETE` selon l’implémentation actuelle ;
- `ContentContext` — `COMPLETE` pour le backend/Backoffice éditorial ;
- `MediaContext` — `COMPLETE` pour les images publiques et documents privés minimaux ;
- `LearningContext` — `COMPLETE` pour formations, Lives, inscriptions et progression ;
- `PaymentContext` — `COMPLETE` pour offres, paiements et fulfillment Learning ;
- IAM (`AuthContext` + `IdentityContext`) — `COMPLETE` pour le socle actuel ;
- `WebContext` pour le shell public ;
- surfaces Web Public, Member et Backoffice ;
- quality gates et baseline Doctrine stabilisés.

### Non livré à ce stade

- les parcours Frontoffice Content et Learning ;
- le modèle métier définitif des cotisations (`ContributionContext`) ;
- l’API v1 ;
- l’application Flutter.

L’intégration KkiaPay est `COMPLETE` côté code (SDK officiel, vérification
serveur et webhook), mais la certification réelle Sandbox reste `PARTIAL`.
L’architecture du stockage persistant est définie via `APP_STORAGE_DIR` avec
`/shared/storage` comme chemin canonique ; son provisionnement reste requis
dans chaque environnement.

## 10. Évolutions futures

L’ordre de progression recommandé est désormais : `CNT-006` Static Pages,
puis les parcours Frontoffice Content/Learning, avant les fonctionnalités
encore planifiées comme les quiz/certificats. `ContributionContext` reste
subordonné à une découverte métier. L’API et Flutter viennent après
stabilisation des contrats métier.

Les exigences détaillées et leur statut sont suivis dans
[`docs/PRODUCT_REQUIREMENTS.md`](PRODUCT_REQUIREMENTS.md), et l’ordre des
phases dans [`docs/ROADMAP.md`](ROADMAP.md).

## 11. Statuts documentaires

Les documents produit utilisent les statuts suivants :

- `IMPLEMENTED` : comportement effectivement livré et vérifiable dans le
  dépôt ;
- `PLANNED` : capacité prévue mais non livrée ;
- `DISCOVERY` : sujet nécessitant encore une validation métier ou technique ;
- `OUT_OF_SCOPE` : explicitement exclu du périmètre courant.

## 12. Références

- [`docs/ARCHITECTURE.md`](ARCHITECTURE.md) pour les frontières et dépendances ;
- [`docs/PERMISSIONS.md`](PERMISSIONS.md) pour les rôles et autorisations ;
- [`docs/UI_UX_GUIDELINES.md`](UI_UX_GUIDELINES.md) pour l’interface ;
- [`docs/specifications_techniques_plateforme_avocats_ci_v3.md`](specifications_techniques_plateforme_avocats_ci_v3.md) pour la cible technique détaillée.

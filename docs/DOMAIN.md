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
| `LearningContext` | formations, contenus pédagogiques, inscriptions et apprentissage | `PLANNED` |
| `PaymentContext` | commandes, transactions et intégration des paiements | `PLANNED` |
| `ContributionContext` | cotisations, situations et reçus après découverte métier | `DISCOVERY` |
| `MediaContext` | médias partagés si la frontière devient réelle | `DISCOVERY` |
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
son titre, slug, chapeau, corps, statut et dates de publication. Les statuts
livrés sont `DRAFT`, `PUBLISHED` et `ARCHIVED` ; la publication et l’archivage
sont des transitions explicites. Le slug est régénéré uniquement tant que
l’actualité est un brouillon. Cette verticale ne livre volontairement aucune
page Frontoffice, API, image de couverture, catégorie ou tag.

## 5. Learning domain

`Training` est le concept central du e-learning.

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
- `LiveDetails` : horaires, fournisseur et identifiants externes d’un `LIVE`.

`Module` est donc rattaché au parcours d’une `COURSE`, tandis que `LIVE` porte
ses propres détails de session.

## 6. Visibilité et accès Learning

La visibilité et l’accès sont indépendants :

```text
TrainingVisibility = PUBLIC
TrainingAccessType = PAID
```

signifie que la fiche est visible dans le catalogue public, mais que le
contenu pédagogique est réservé aux utilisateurs autorisés. `Enrollment` et
une policy serveur contrôlent la consommation effective.

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

## 12. Références

- [`docs/ARCHITECTURE.md`](ARCHITECTURE.md) ;
- [`docs/PERMISSIONS.md`](PERMISSIONS.md) ;
- [`docs/specifications_techniques_plateforme_avocats_ci_v3.md`](specifications_techniques_plateforme_avocats_ci_v3.md).

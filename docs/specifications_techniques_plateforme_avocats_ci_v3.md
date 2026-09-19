---
title: "Spécifications techniques V3"
subtitle: "Plateforme numérique des avocats de Côte d'Ivoire"
author: "Document de référence projet"
date: "17 septembre 2026"
lang: fr-FR
toc-title: "Table des matières"
---

# 1. Objet du document

Cette V3 transforme le cahier des charges V2 en **référentiel technique de réalisation**. Elle doit servir de base commune à la maîtrise d'ouvrage, à l'équipe de développement, à Codex et à Manus AI.

Le document précise :

- les frontières métier ;
- les surfaces applicatives ;
- les agrégats, entités et Value Objects ;
- les enums et états principaux ;
- les Commands, Queries et événements ;
- les contrats de repositories ;
- les permissions ;
- les routes Twig et API ;
- les intégrations YouTube et paiement ;
- les principes de persistance ;
- les règles de sécurité ;
- les tests ;
- le découpage en Epics et tickets ;
- les règles de collaboration avec Codex et Manus AI.

Cette V3 **ne remplace pas la validation métier**. Le module `ContributionContext`, en particulier, reste soumis à un audit du fonctionnement actuel des cotisations avant gel du modèle définitif.

# 2. Vision technique retenue

La plateforme est un **monolithe modulaire Symfony** exposant quatre surfaces principales :

```text
                         INTERNET
                            |
          +-----------------+------------------+
          |                 |                  |
          v                 v                  v
   Frontoffice Twig    Espace membre Twig     API v1
      public             authentifié          Flutter
          |                 |                  |
          +-----------------+------------------+
                            |
                         Symfony
                            |
      +---------------------+---------------------+
      |                     |                     |
      v                     v                     v
 ContentContext     ContributionContext     LearningContext
      |                     |                     |
      +-----------+---------+----------+----------+
                  |                    |
                  v                    v
          Services transverses   Backoffice Twig
       Identity / Payment /         sécurisé
       Media / Notification /
       Audit / Search
```

Les contenus éditoriaux et les fiches de formation sont **publiquement consultables** lorsqu'ils sont publiés et configurés comme publics. L'accès aux ressources protégées - documents privés, vidéos pédagogiques, progression, Live payant, cotisations - reste soumis aux règles d'autorisation du backend.

# 3. Stack technique cible

## 3.1 Backend

- PHP 8.3 ou version validée au démarrage ;
- Symfony 7.4 ;
- Doctrine ORM 3 ;
- Doctrine Migrations ;
- MySQL 8.x ou version managée équivalente ;
- Symfony Messenger ;
- Symfony Security ;
- Symfony Validator ;
- Symfony Form pour les interfaces Twig lorsque pertinent ;
- Twig pour Frontoffice, Espace membre et Backoffice ;
- API JSON versionnée pour Flutter et intégrations.

## 3.2 Frontend Web

- Twig ;
- composants réutilisables ;
- JavaScript progressif uniquement lorsque nécessaire ;
- CSS structuré autour d'un design system ;
- responsive mobile-first ;
- accessibilité comme exigence transversale.

## 3.3 Mobile

- Flutter dans une phase ultérieure ;
- consommation de `/api/v1` ;
- aucune règle métier critique dupliquée dans le client mobile.

## 3.4 Infrastructure externe

- YouTube / YouTube Live pour la vidéo initiale ;
- stockage objet ou stockage privé pour documents et images ;
- prestataire(s) de paiement à confirmer ;
- SMTP ou fournisseur transactionnel pour email ;
- notifications push ultérieures.

# 4. Principes structurants

## 4.1 Monolithe modulaire

Les modules sont déployés ensemble mais doivent être organisés comme des sous-domaines isolés.

```text
src/
+-- IdentityContext/
+-- ContentContext/
+-- ContributionContext/
+-- LearningContext/
+-- PaymentContext/
+-- NotificationContext/
+-- MediaContext/
+-- AuditContext/
+-- SharedContext/
```

`SharedContext` doit rester minimal. Une classe ne doit pas y être déplacée simplement parce qu'elle est utilisée deux fois.

## 4.2 DDD pragmatique

Le domaine contient les règles métier. Les contrôleurs ne doivent pas porter de logique métier.

Pour une écriture :

```text
Controller / Console / API
          |
          v
       Command
          |
          v
   CommandHandler
          |
          v
        Domain
          |
          v
Repository Interface
          |
          v
Infrastructure Adapter
```

Pour une lecture :

```text
Controller / API
       |
       v
      Query
       |
       v
  QueryHandler
       |
       v
Read Model / Projection
       |
       v
ViewModel / JSON response
```

## 4.3 CQRS pragmatique

CQRS est utilisé pour rendre explicites les cas d'usage, pas pour imposer une complexité artificielle. Les lectures complexes peuvent utiliser des repositories de lecture dédiés sans reconstituer les agrégats.

## 4.4 Source de vérité

Le backend Symfony reste la source de vérité pour :

- permissions ;
- inscriptions ;
- cotisations ;
- paiements validés ;
- accès aux formations ;
- état des certificats ;
- publication des contenus.

YouTube, Flutter et Twig sont des interfaces ou fournisseurs, jamais la source de vérité métier.

# 5. Surfaces applicatives

## 5.1 Frontoffice Twig public

Le Frontoffice est une surface majeure du produit. Il permet sans authentification, selon la visibilité :

- accueil ;
- actualités ;
- événements ;
- vidéos éditoriales ;
- galeries ;
- documents publics ;
- catalogue e-learning ;
- fiches de formations classiques ;
- fiches de formations Live ;
- calendrier des Lives ;
- pages institutionnelles ;
- recherche publique ;
- connexion et inscription.

Exemples de routes :

```text
GET /
GET /actualites
GET /actualites/{slug}
GET /evenements
GET /evenements/{slug}
GET /videos
GET /galeries
GET /galeries/{slug}
GET /formations
GET /formations/{slug}
GET /lives
GET /recherche
```

## 5.2 Espace membre Twig

L'espace membre nécessite une authentification.

```text
GET /espace
GET /espace/profil
GET /espace/cotisations
GET /espace/paiements
GET /espace/formations
GET /espace/formations/{id}
GET /espace/lives
GET /espace/certificats
GET /espace/notifications
```

## 5.3 Backoffice Twig

Le Backoffice est protégé par permissions. Il ne doit pas se limiter à un rôle unique `ROLE_ADMIN`.

Sections :

```text
/admin
/admin/contenus/*
/admin/evenements/*
/admin/galeries/*
/admin/cotisations/*
/admin/formations/*
/admin/lives/*
/admin/paiements/*
/admin/utilisateurs/*
/admin/permissions/*
/admin/audit/*
```

## 5.4 API v1

L'API est destinée au mobile Flutter et à certaines intégrations.

```text
/api/v1/auth/*
/api/v1/content/*
/api/v1/events/*
/api/v1/galleries/*
/api/v1/trainings/*
/api/v1/enrollments/*
/api/v1/contributions/*
/api/v1/payments/*
/api/v1/notifications/*
```

# 6. Structure d'un Bounded Context

Structure de référence :

```text
<Context>/
+-- Domain/
|   +-- Model/
|   +-- ValueObject/
|   +-- Enum/
|   +-- Event/
|   +-- Repository/
|   +-- Service/
|   +-- Policy/
|
+-- Application/
|   +-- Command/
|   +-- CommandHandler/
|   +-- Query/
|   +-- QueryHandler/
|   +-- DTO/
|   +-- Service/
|
+-- Infrastructure/
|   +-- Persistence/
|   +-- External/
|   +-- Storage/
|   +-- Messaging/
|
+-- Presenter/
    +-- Frontoffice/
    +-- Member/
    +-- Backoffice/
    +-- Api/
```

Cette structure peut être simplifiée pour un contexte réduit. Les dossiers ne doivent pas être créés s'ils sont vides.

# 7. Context Map

```text
IdentityContext
   |
   +----------------------+-----------------------+
   |                      |                       |
   v                      v                       v
ContentContext    ContributionContext      LearningContext
                                              |
                                              v
                                        PaymentContext

ContentContext ---------> MediaContext
LearningContext --------> MediaContext
ContributionContext ----> PaymentContext (si paiement en ligne activé)

Tous les contextes -----> NotificationContext
Tous les contextes -----> AuditContext pour événements sensibles
```

Règle : chaque contexte conserve ses règles métier. `PaymentContext` confirme une transaction ; `LearningContext` décide qu'une transaction confirmée donne lieu à une inscription.

# 8. IdentityContext

## 8.1 Responsabilités

- compte utilisateur ;
- authentification ;
- statut du compte ;
- rôles ;
- permissions ;
- profil ;
- association éventuelle au dossier professionnel d'un avocat ;
- réinitialisation de mot de passe ;
- vérification email si retenue.

## 8.2 Agrégat User

```text
User
+-- UserId
+-- Email
+-- PasswordHash
+-- AccountStatus
+-- roles
+-- permissions additionnelles éventuelles
+-- createdAt
+-- updatedAt
```

## 8.3 Value Objects

- `UserId` ;
- `EmailAddress` ;
- `DisplayName` ;
- `PhoneNumber` si nécessaire ;
- `PasswordHash` en infrastructure/sécurité, jamais mot de passe en clair.

## 8.4 Enums

```text
AccountStatus
- PENDING
- ACTIVE
- SUSPENDED
- DISABLED
```

## 8.5 Commands

- `RegisterUser` ;
- `ActivateUser` ;
- `SuspendUser` ;
- `UpdateProfile` ;
- `AssignRole` ;
- `RevokeRole` ;
- `GrantPermission` ;
- `RevokePermission`.

## 8.6 Queries

- `GetCurrentUser` ;
- `GetUserProfile` ;
- `SearchUsers` ;
- `GetUserPermissions`.

## 8.7 Domain events

- `UserRegistered` ;
- `UserActivated` ;
- `UserSuspended` ;
- `UserRoleChanged`.

# 9. Autorisation et permissions

## 9.1 Convention

```text
<context>.<resource>.<action>
```

Exemples :

```text
content.news.view
content.news.create
content.news.update
content.news.publish
content.event.manage
content.gallery.manage

contribution.own.view
contribution.all.view
contribution.assessment.create
contribution.assessment.adjust
contribution.payment.record
contribution.receipt.issue

learning.training.create
learning.training.update
learning.training.publish
learning.live.manage
learning.enrollment.view
learning.enrollment.manage
learning.certificate.issue

payment.transaction.view
payment.refund.manage

audit.view
```

## 9.2 Stratégie Symfony

- `access_control` pour les frontières générales ;
- Voters ou Policies pour les décisions liées à une ressource ;
- vérification côté serveur systématique ;
- aucun bouton masqué côté Twig ne doit être considéré comme mesure de sécurité.

## 9.3 Matrice initiale

| Profil | Content | Cotisations | Learning | Système |
|---|---|---|---|---|
| Visiteur | lecture publique | aucun | catalogue public | aucun |
| Avocat | lecture | ses données | ses formations | profil |
| Responsable communication | gestion éditoriale | aucun | selon permission | limité |
| Responsable cotisations | lecture publique | gestion autorisée | aucun par défaut | limité |
| Responsable formation | lecture publique | aucun | gestion autorisée | limité |
| Formateur | lecture | aucun | ses formations selon droits | limité |
| Admin technique | selon attribution | selon attribution | selon attribution | administration technique |

Les données financières ne doivent pas être ouvertes automatiquement à un « super admin » métier sans validation de gouvernance.

# 10. ContentContext

## 10.1 Objectif

Publier et gérer les contenus institutionnels et professionnels visibles principalement sur le Frontoffice public.

## 10.2 Agrégat Content

Le tronc commun peut être modélisé par un agrégat `Content` ou des agrégats spécialisés partageant des Value Objects. La décision finale dépendra des différences de cycle de vie.

Attributs communs :

```text
ContentId
ContentType
Title
Slug
Excerpt
Body
PublicationStatus
Visibility
CoverMediaId
AuthorId
publishedAt
createdAt
updatedAt
```

## 10.3 Enums

```text
ContentType
- NEWS
- EVENT
- VIDEO
- PHOTO_GALLERY
- ANNOUNCEMENT
- DOCUMENT

PublicationStatus
- DRAFT
- SCHEDULED
- PUBLISHED
- ARCHIVED

ContentVisibility
- PUBLIC
- MEMBERS_ONLY
- RESTRICTED
```

## 10.4 EventDetails

```text
EventDetails
+-- startsAt
+-- endsAt
+-- timezone
+-- locationName
+-- address
+-- registrationMode
+-- registrationUrl
+-- capacity optional
```

## 10.5 VideoDetails

```text
VideoDetails
+-- provider
+-- providerVideoId
+-- thumbnail
+-- duration optional
```

## 10.6 PhotoGallery

```text
PhotoGallery
+-- GalleryId
+-- title
+-- slug
+-- publicationStatus
+-- visibility
+-- photos[]

GalleryPhoto
+-- MediaId
+-- caption
+-- position
```

## 10.7 Commands

- `CreateNews` ;
- `UpdateNews` ;
- `PublishContent` ;
- `ScheduleContentPublication` ;
- `ArchiveContent` ;
- `CreateEvent` ;
- `UpdateEvent` ;
- `CreateVideoContent` ;
- `CreateGallery` ;
- `AddGalleryPhoto` ;
- `ReorderGalleryPhotos`.

## 10.8 Queries

- `ListPublishedNews` ;
- `GetPublicContentBySlug` ;
- `ListUpcomingEvents` ;
- `ListPublishedVideos` ;
- `GetGalleryBySlug` ;
- `SearchPublicContent` ;
- `SearchBackofficeContent`.

## 10.9 Domain events

- `ContentPublished` ;
- `ContentArchived` ;
- `EventPublished` ;
- `GalleryPublished`.

## 10.10 Règles métier

- un brouillon n'est jamais visible dans le Frontoffice public ;
- un contenu `MEMBERS_ONLY` nécessite une session authentifiée ;
- le slug doit être stable et unique dans le scope défini ;
- une date de publication future produit un contenu programmé ;
- une galerie publiée ne doit afficher que les médias valides et accessibles.

# 11. Frontoffice Content

## 11.1 Pages

- `/actualites` : pagination ;
- `/actualites/{slug}` : détail ;
- `/evenements` : événements à venir et passés ;
- `/evenements/{slug}` ;
- `/videos` ;
- `/galeries` ;
- `/galeries/{slug}`.

## 11.2 ViewModels

Le Frontoffice utilise des ViewModels ou Read Models dédiés plutôt que d'exposer directement les entités Doctrine à Twig.

Exemple :

```text
NewsCardView
- title
- slug
- excerpt
- coverUrl
- publishedAt

NewsDetailView
- title
- bodyHtml
- coverUrl
- authorDisplayName
- publishedAt
- relatedContents[]
```

# 12. ContributionContext - statut provisoire

## 12.1 Principe

Ce contexte doit être spécifié en deux temps :

1. modèle d'intégration provisoire ;
2. modèle définitif après audit du système existant.

Il est interdit de figer des règles de calcul de cotisation sans validation métier.

## 12.2 Questions de découverte obligatoires

- Quelle entité appelle la cotisation ?
- Quelles périodes existent ?
- Existe-t-il plusieurs natures de cotisation ?
- Le montant est-il fixe, calculé ou individualisé ?
- Existe-t-il des exemptions, remises ou pénalités ?
- Les paiements partiels sont-ils autorisés ?
- Comment gérer les arriérés ?
- Comment sont produits les reçus ?
- Quel système existe déjà ?
- Quelle donnée est la source de vérité ?
- Faut-il importer, synchroniser ou remplacer ?
- Quelles actions nécessitent une piste d'audit renforcée ?

## 12.3 Modèle conceptuel provisoire

```text
LawyerProfile
     |
     v
ProfessionalMembership
     |
     v
ContributionAssessment
     |
     +-- ContributionPeriod
     +-- AmountDue
     +-- DueDate
     +-- ContributionStatus
     |
     +-- ContributionAllocation(s)
            |
            v
          Payment
```

## 12.4 Agrégat ContributionAssessment

Attributs provisoires :

```text
ContributionAssessmentId
LawyerId
ContributionPeriodId
ContributionTypeId optional
Money amountDue
Money amountPaid
DueDate
ContributionStatus
version
createdAt
updatedAt
```

## 12.5 Enums provisoires

```text
ContributionStatus
- DRAFT
- ISSUED
- PARTIALLY_PAID
- PAID
- OVERDUE
- CANCELLED
- ADJUSTED
```

## 12.6 Commands provisoires

- `IssueContributionAssessment` ;
- `AdjustContributionAssessment` ;
- `CancelContributionAssessment` ;
- `RecordContributionPayment` ;
- `AllocatePaymentToContribution` ;
- `IssueContributionReceipt` ;
- `ImportContributionData`.

## 12.7 Queries

- `GetOwnContributionSituation` ;
- `ListOwnContributionHistory` ;
- `SearchLawyerContributionSituations` ;
- `GetContributionAssessment` ;
- `GetContributionDashboard`.

## 12.8 Domain events

- `ContributionIssued` ;
- `ContributionAdjusted` ;
- `ContributionPaymentRecorded` ;
- `ContributionFullyPaid` ;
- `ContributionOverdue` ;
- `ContributionReceiptIssued`.

## 12.9 Concurrence et audit

Toute modification financière sensible doit :

- être atomique ;
- conserver l'auteur de l'action ;
- conserver la date ;
- conserver la justification lorsqu'elle existe ;
- éviter les suppressions physiques ;
- utiliser un mécanisme de version/concurrence si des opérateurs peuvent modifier la même situation.

# 13. LearningContext - modèle général

## 13.1 Agrégat Training

`Training` est le concept central du e-learning.

```text
Training
+-- TrainingId
+-- TrainingType
+-- Title
+-- Slug
+-- ShortDescription
+-- Description
+-- TrainingStatus
+-- TrainingVisibility
+-- TrainingAccessType
+-- Money price
+-- CategoryId
+-- instructors[]
+-- CoverMediaId
+-- CertificatePolicy
+-- publishedAt
+-- createdAt
+-- updatedAt
```

## 13.2 Enums

```text
TrainingType
- COURSE
- LIVE

TrainingStatus
- DRAFT
- PUBLISHED
- ARCHIVED

TrainingVisibility
- PUBLIC
- MEMBERS_ONLY
- UNLISTED

TrainingAccessType
- FREE
- PAID
```

Règle importante : `TrainingVisibility` et `TrainingAccessType` sont indépendants.

Exemple :

```text
visibility = PUBLIC
accessType = PAID
```

signifie que tout visiteur peut consulter la fiche, mais seuls les utilisateurs autorisés peuvent consommer le contenu.

# 14. Training COURSE

## 14.1 Structure

```text
Training COURSE
     |
     +-- Module 1
     |     +-- LearningContent
     |     +-- LearningContent
     |
     +-- Module 2
           +-- LearningContent
```

## 14.2 Module

```text
TrainingModule
+-- ModuleId
+-- TrainingId
+-- title
+-- description
+-- position
+-- status
```

## 14.3 LearningContent

```text
LearningContent
+-- LearningContentId
+-- ModuleId
+-- LearningContentType
+-- title
+-- position
+-- publicationStatus
+-- completionRule
```

Types :

```text
VIDEO
DOCUMENT
TEXT
QUIZ
```

## 14.4 VideoContent

```text
VideoContent
+-- provider = YOUTUBE
+-- providerVideoId
+-- duration optional
+-- allowResume
```

## 14.5 DocumentContent

```text
DocumentContent
+-- MediaId
+-- downloadPolicy
+-- displayMode
```

## 14.6 TextContent

Contenu HTML nettoyé, produit dans le Backoffice et présenté dans le lecteur pédagogique.

# 15. Training LIVE

## 15.1 Principe

Le Live est une formation autonome.

```text
Training
 type = LIVE
     |
     v
 LiveDetails
```

## 15.2 LiveDetails

```text
LiveDetails
+-- TrainingId
+-- scheduledStartAt
+-- scheduledEndAt optional
+-- startedAt optional
+-- endedAt optional
+-- LiveStatus
+-- provider
+-- providerBroadcastId
+-- providerStreamId optional
+-- providerVideoId optional
+-- replayStatus
+-- replayAvailableUntil optional
```

## 15.3 Enums

```text
LiveStatus
- DRAFT
- SCHEDULED
- READY
- LIVE
- COMPLETED
- CANCELLED
- FAILED

ReplayStatus
- NONE
- PROCESSING
- AVAILABLE
- EXPIRED
```

Les statuts métier ne doivent pas être une copie exacte des statuts YouTube. Un mapper d'infrastructure traduit les états externes en états métier.

# 16. Enrollment

## 16.1 Agrégat / entité

```text
Enrollment
+-- EnrollmentId
+-- UserId
+-- TrainingId
+-- EnrollmentStatus
+-- enrolledAt
+-- accessStartsAt optional
+-- accessEndsAt optional
+-- completedAt optional
+-- source
```

## 16.2 Enums

```text
EnrollmentStatus
- PENDING
- ACTIVE
- COMPLETED
- CANCELLED
- EXPIRED

EnrollmentSource
- FREE_REGISTRATION
- ORDER
- ADMIN_GRANT
- IMPORT
```

## 16.3 Contraintes

- un utilisateur ne doit pas avoir deux inscriptions actives identiques sans motif métier ;
- une inscription payante ne devient active qu'après règle métier validant le paiement ;
- une formation gratuite peut créer directement l'inscription ;
- la révocation administrative doit être auditée.

# 17. LearningProgress

```text
LearningProgress
+-- UserId
+-- TrainingId
+-- LearningContentId
+-- percentage
+-- completed
+-- lastPositionSeconds optional
+-- startedAt
+-- lastActivityAt
+-- completedAt optional
```

Règles :

- la progression ne constitue pas une preuve absolue de visionnage ;
- elle doit être idempotente ;
- une mise à jour répétée au même niveau ne doit pas créer des doublons ;
- le calcul de complétion du Training doit être centralisé dans une Policy ou un Domain Service.

# 18. Quiz

## 18.1 Modèle

```text
Quiz
+-- QuizId
+-- TrainingId / ContentId
+-- title
+-- passingScore
+-- maxAttempts optional
+-- questions[]

Question
+-- QuestionId
+-- statement
+-- type
+-- answers[]

QuizAttempt
+-- QuizAttemptId
+-- UserId
+-- QuizId
+-- score
+-- status
+-- startedAt
+-- submittedAt
```

## 18.2 Première version

Le MVP peut se limiter aux questions à choix unique ou multiple. Les réponses rédactionnelles demandent un processus de correction séparé.

# 19. Certificate

```text
Certificate
+-- CertificateId
+-- CertificateNumber
+-- VerificationCode
+-- UserId
+-- TrainingId
+-- issuedAt
+-- revokedAt optional
+-- status
```

Règles :

- numéro unique ;
- code de vérification non prédictible ;
- page publique minimale ;
- révocation possible sans suppression ;
- émission conditionnée par la `CertificatePolicy`.

# 20. Commands du LearningContext

Liste initiale :

```text
CreateTraining
UpdateTraining
PublishTraining
ArchiveTraining
SetTrainingPrice
AddTrainingInstructor
CreateTrainingModule
ReorderTrainingModules
AddVideoContent
AddDocumentContent
AddTextContent
PublishLearningContent
ScheduleLive
UpdateLiveSchedule
CancelLive
SynchronizeLiveState
EnrollUserForFreeTraining
GrantEnrollment
RevokeEnrollment
RecordLearningProgress
SubmitQuizAttempt
MarkTrainingCompleted
IssueCertificate
RevokeCertificate
```

# 21. Queries du LearningContext

```text
ListPublicTrainings
GetPublicTrainingBySlug
ListUpcomingPublicLives
GetTrainingLearningPage
GetUserTrainings
GetUserTraining
GetUserProgress
GetUserCertificates
GetCertificateVerification
SearchBackofficeTrainings
GetTrainingParticipants
GetLiveParticipants
GetLearningDashboard
```

# 22. Domain events du LearningContext

```text
TrainingCreated
TrainingPublished
TrainingArchived
LiveScheduled
LiveStarted
LiveCompleted
ReplayAvailable
EnrollmentCreated
EnrollmentActivated
EnrollmentRevoked
LearningContentCompleted
TrainingCompleted
QuizPassed
CertificateIssued
CertificateRevoked
```

Les événements métier ne doivent pas dépendre de classes YouTube, Doctrine ou HTTP.

# 23. Repository contracts - Learning

Interfaces de domaine possibles :

```text
TrainingRepository
- get(TrainingId): Training
- save(Training): void
- existsBySlug(Slug): bool

EnrollmentRepository
- get(EnrollmentId): Enrollment
- findActive(UserId, TrainingId): ?Enrollment
- save(Enrollment): void

CertificateRepository
- findByVerificationCode(string): ?Certificate
- save(Certificate): void
```

Les recherches de catalogue, dashboards et tableaux administratifs peuvent utiliser des Read Repositories séparés.

# 24. Accès public vs accès pédagogique

## 24.1 Fiche publique

Une formation `PUBLISHED + PUBLIC` est visible dans le catalogue public.

## 24.2 Consommation

```text
Visitor
  |
  v
Public Training Page
  |
  +-- FREE --> authenticate --> create/resolve enrollment --> learning area
  |
  +-- PAID --> authenticate --> order/payment --> active enrollment --> learning area
```

## 24.3 Policy d'accès

Une `TrainingAccessPolicy` décide si un utilisateur peut :

- consulter la fiche ;
- s'inscrire ;
- accéder au lecteur ;
- rejoindre le Live ;
- télécharger un document ;
- voir le replay.

Le contrôleur ne doit pas reproduire ces règles.

# 25. YouTube - architecture d'intégration

## 25.1 Abstraction

```text
VideoProviderInterface
+-- getVideoMetadata()
+-- createLiveBroadcast()
+-- createLiveStream()
+-- bindBroadcastToStream()
+-- transitionBroadcast()
+-- getBroadcastState()
+-- getReplayVideoId()
```

Implémentation initiale :

```text
YouTubeVideoProvider
```

## 25.2 Données externes

Ne jamais stocker des tokens OAuth en clair dans la base. Utiliser un mécanisme de secrets/chiffrement adapté.

Données métier stockables :

```text
provider
providerBroadcastId
providerStreamId
providerVideoId
providerChannelId
providerVisibility
lastSynchronizedAt
```

## 25.3 Workflow de programmation

```text
Admin schedules LIVE
        |
        v
ScheduleLive command
        |
        v
Training/LiveDetails saved
        |
        v
Async message: ProvisionYouTubeLive
        |
        v
YouTube API
  create broadcast
  create/reuse stream
  bind
        |
        v
Persist external identifiers
```

## 25.4 Synchronisation

```text
Scheduled worker / command
        |
        v
Get YouTube broadcast state
        |
        v
Map external state -> LiveStatus
        |
        v
Persist transition
        |
        +--> domain event if changed
```

## 25.5 IFrame Player

Le lecteur Web utilise l'intégration YouTube IFrame. Les événements de lecture peuvent alimenter une progression indicative, mais ne doivent pas être traités comme preuve forte de présence.

## 25.6 Public / unlisted / private

La confidentialité YouTube est distincte de l'autorisation applicative.

```text
YouTube visibility != Platform access permission
```

Pour une offre payante, le backend vérifie l'inscription avant d'afficher l'interface de lecture. Un lien YouTube partagé peut toutefois contourner partiellement l'expérience applicative si la vidéo est accessible directement ; cette limite doit être acceptée ou conduire ultérieurement à un fournisseur vidéo offrant des contrôles plus stricts.

# 26. PaymentContext

## 26.1 Responsabilités

- représenter les demandes de paiement ;
- intégrer les prestataires ;
- traiter les callbacks/webhooks ;
- appliquer l'idempotence ;
- enregistrer les statuts ;
- exposer des événements fiables aux contextes métier.

## 26.2 Agrégat Order

```text
Order
+-- OrderId
+-- UserId
+-- OrderStatus
+-- Currency
+-- totalAmount
+-- items[]
+-- createdAt
+-- paidAt optional
```

## 26.3 OrderItem

```text
OrderItem
+-- type
+-- referenceId
+-- labelSnapshot
+-- unitPrice
+-- quantity
+-- total
```

Le snapshot du libellé et du prix évite qu'une modification ultérieure du catalogue change l'historique de commande.

## 26.4 PaymentTransaction

```text
PaymentTransaction
+-- PaymentId
+-- OrderId
+-- provider
+-- providerReference
+-- amount
+-- PaymentStatus
+-- initiatedAt
+-- confirmedAt optional
+-- failureReason optional
```

## 26.5 Enums

```text
OrderStatus
- PENDING
- PAID
- CANCELLED
- REFUNDED

PaymentStatus
- INITIATED
- PENDING
- SUCCEEDED
- FAILED
- CANCELLED
- REFUNDED
```

## 26.6 Commands

- `CreateOrder` ;
- `InitiatePayment` ;
- `HandlePaymentWebhook` ;
- `ConfirmPayment` ;
- `MarkPaymentFailed` ;
- `RequestRefund` ;
- `ConfirmRefund`.

## 26.7 Domain events

- `OrderCreated` ;
- `PaymentInitiated` ;
- `PaymentSucceeded` ;
- `PaymentFailed` ;
- `PaymentRefunded`.

## 26.8 Idempotence

Les webhooks peuvent être envoyés plusieurs fois. Il faut conserver un identifiant stable de l'événement du fournisseur ou calculer une clé d'idempotence métier.

Le handler doit pouvoir recevoir deux fois le même événement sans activer deux inscriptions ni comptabiliser deux paiements.

# 27. Interaction Payment -> Learning

```text
PaymentSucceeded
       |
       v
Application listener / process manager
       |
       v
ActivateEnrollmentForPaidTraining
       |
       v
LearningContext
```

Le handler Payment ne doit pas écrire directement dans les tables du LearningContext.

# 28. Interaction Payment -> Contribution

À définir après audit. Deux modèles sont possibles :

1. ContributionContext crée une intention de paiement via PaymentContext puis alloue le paiement confirmé ;
2. un système externe reste source de vérité et la plateforme ne fait que synchroniser.

Aucune option ne doit être choisie sans analyse de l'existant.

# 29. MediaContext

## 29.1 Modèle

```text
Media
+-- MediaId
+-- MediaType
+-- StorageType
+-- originalName
+-- storedName/key
+-- mimeType
+-- size
+-- checksum optional
+-- visibility
+-- createdAt
```

Enums :

```text
MediaType
- IMAGE
- DOCUMENT
- AUDIO
- OTHER

MediaVisibility
- PUBLIC
- PRIVATE
```

## 29.2 Fichiers privés

Un document privé ne doit pas être exposé via une URL permanente publique.

Options :

- téléchargement à travers un contrôleur autorisé ;
- URL signée courte durée avec stockage objet ;
- proxy sécurisé.

## 29.3 Validation

- taille maximale configurable ;
- whitelist de types ;
- contrôle MIME réel ;
- nom de fichier généré côté serveur ;
- antivirus si infrastructure disponible ;
- métadonnées nettoyées pour certains médias si nécessaire.

# 30. NotificationContext

## 30.1 Canaux

```text
EMAIL
IN_APP
PUSH   (phase ultérieure)
SMS    (optionnel)
```

## 30.2 Notification

```text
Notification
+-- NotificationId
+-- UserId
+-- type
+-- channel
+-- payload
+-- status
+-- scheduledAt
+-- sentAt optional
+-- readAt optional
```

## 30.3 Cas d'usage

- inscription confirmée ;
- paiement confirmé ;
- Live programmé ;
- rappel avant Live ;
- replay disponible ;
- certificat disponible ;
- échéance de cotisation ;
- contenu institutionnel important.

# 31. AuditContext

## 31.1 Objectif

Créer une piste d'audit fonctionnelle distincte des logs techniques.

```text
AuditEntry
+-- id
+-- actorUserId optional
+-- action
+-- targetType
+-- targetId
+-- occurredAt
+-- context
+-- metadata
+-- requestCorrelationId optional
```

Actions sensibles :

```text
ROLE_CHANGED
PERMISSION_CHANGED
TRAINING_PUBLISHED
PAYMENT_CONFIRMED
PAYMENT_REFUNDED
CONTRIBUTION_ADJUSTED
CONTRIBUTION_PAYMENT_RECORDED
RECEIPT_ISSUED
CERTIFICATE_REVOKED
```

L'audit doit être append-only dans la mesure du possible.

# 32. Search

Le MVP peut utiliser MySQL et des indexes adaptés. Une solution dédiée (Meilisearch, Elasticsearch, OpenSearch) n'est à envisager que si le besoin le justifie.

Recherche publique :

- actualités ;
- événements ;
- vidéos ;
- formations ;
- documents publics.

Recherche administrative : permissions obligatoires.

# 33. Routes Frontoffice recommandées

```text
app_home                         GET  /
content_news_index               GET  /actualites
content_news_show                GET  /actualites/{slug}
content_event_index              GET  /evenements
content_event_show               GET  /evenements/{slug}
content_video_index              GET  /videos
content_gallery_index            GET  /galeries
content_gallery_show             GET  /galeries/{slug}
learning_catalog                 GET  /formations
learning_training_show           GET  /formations/{slug}
learning_live_index              GET  /lives
search_public                    GET  /recherche
```

# 34. Routes Espace membre recommandées

```text
member_dashboard                 GET  /espace
member_profile                   GET  /espace/profil
member_contributions             GET  /espace/cotisations
member_contribution_show         GET  /espace/cotisations/{id}
member_payments                  GET  /espace/paiements
member_trainings                 GET  /espace/formations
member_training_learn            GET  /espace/formations/{id}/apprendre
member_live_join                 GET  /espace/lives/{id}/rejoindre
member_certificates              GET  /espace/certificats
member_notifications             GET  /espace/notifications
```

# 35. Routes Backoffice recommandées

Les noms doivent rester par contexte.

```text
admin_dashboard
admin_content_news_*
admin_content_event_*
admin_content_gallery_*
admin_learning_training_*
admin_learning_live_*
admin_learning_enrollment_*
admin_contribution_assessment_*
admin_contribution_payment_*
admin_identity_user_*
admin_system_audit_*
```

# 36. API v1 - conventions

## 36.1 Versionnement

Préfixe :

```text
/api/v1
```

## 36.2 Réponses

Les réponses ne doivent pas exposer directement les entités Doctrine.

Exemple :

```json
{
  "data": {
    "id": "...",
    "title": "Droit OHADA",
    "type": "COURSE",
    "accessType": "PAID"
  }
}
```

## 36.3 Erreurs

Format cohérent :

```json
{
  "error": {
    "code": "TRAINING_NOT_FOUND",
    "message": "Formation introuvable",
    "details": {}
  }
}
```

## 36.4 Pagination

Utiliser une convention unique, par exemple :

```json
{
  "data": [],
  "meta": {
    "page": 1,
    "perPage": 20,
    "total": 140,
    "pages": 7
  }
}
```

# 37. Endpoints API initiaux

## Public

```text
GET /api/v1/content/news
GET /api/v1/content/news/{slug}
GET /api/v1/events
GET /api/v1/events/{slug}
GET /api/v1/trainings
GET /api/v1/trainings/{slug}
GET /api/v1/lives/upcoming
```

## Authentifiés

```text
GET  /api/v1/me
GET  /api/v1/me/trainings
GET  /api/v1/me/contributions
GET  /api/v1/me/certificates
POST /api/v1/trainings/{id}/enroll
POST /api/v1/orders
POST /api/v1/payments/{provider}/initiate
POST /api/v1/learning/content/{id}/progress
POST /api/v1/quizzes/{id}/attempts
```

## Administration

Les endpoints admin API ne sont ajoutés que s'il existe un besoin réel. Le Backoffice Twig peut utiliser directement les use cases applicatifs.

# 38. Modèle relationnel indicatif

Tables principales potentielles :

```text
identity_user
identity_role
identity_permission
identity_user_role
identity_role_permission

content_content
content_category
content_tag
content_content_tag
content_event_details
content_video_details
content_gallery
content_gallery_photo

learning_training
learning_training_instructor
learning_module
learning_content
learning_video_content
learning_document_content
learning_live_details
learning_enrollment
learning_progress
learning_quiz
learning_question
learning_answer
learning_quiz_attempt
learning_certificate

payment_order
payment_order_item
payment_transaction
payment_webhook_event

contribution_period
contribution_assessment
contribution_payment_allocation
contribution_receipt

media_media
notification_notification
audit_entry
```

Ce schéma est indicatif. Le mapping Doctrine définitif doit être produit à partir des agrégats, pas l'inverse.

# 39. Identifiants

Recommandation : utiliser UUID/ULID pour les identifiants exposés entre contextes et via API, selon standard projet choisi.

Règles :

- pas d'hypothèse métier basée sur la valeur d'un ID ;
- IDs immuables ;
- ne pas exposer inutilement des séquences internes ;
- Value Object d'ID dans le domaine lorsque cela améliore la robustesse.

# 40. Money

Un `Money` Value Object doit encapsuler :

```text
amount
currency
```

Règles :

- aucun float ;
- montant stocké en unité mineure si approprié ;
- devise explicite ;
- opérations monétaires contrôlées ;
- la devise de référence initiale peut être XOF, mais le modèle ne doit pas l'encoder en dur partout.

# 41. Date et heure

- stocker en UTC côté backend/base lorsque possible ;
- conserver le fuseau métier si nécessaire ;
- afficher selon le fuseau configuré ;
- pour les Lives, la date programmée doit être explicite et non dépendre du navigateur.

# 42. Transactions et cohérence

Un CommandHandler qui modifie un agrégat doit s'exécuter dans une transaction adaptée.

Les appels externes ne doivent pas être placés au milieu d'une transaction longue.

Pattern recommandé pour certaines intégrations :

```text
Transaction métier
   |
   +-- persist domain state
   +-- persist outbox/message intent
   |
 commit
   |
 async external call
```

Une Outbox peut être ajoutée si la fiabilité de publication d'événements l'exige.

# 43. Messenger

## 43.1 Usage

Utiliser Messenger pour :

- provisioning YouTube ;
- synchronisation YouTube ;
- emails ;
- rappels ;
- génération de certificats ;
- imports lourds ;
- traitements statistiques ;
- appels externes non bloquants.

## 43.2 Règles

- messages sérialisables ;
- transporter des IDs et valeurs simples ;
- handlers idempotents autant que possible ;
- retries configurés ;
- failure transport activé ;
- supervision des messages échoués.

# 44. Scheduler / tâches planifiées

Tâches possibles :

```text
SynchronizeUpcomingYouTubeLives
SendLiveReminders
MarkOverdueContributions
PublishScheduledContents
ExpireEnrollments
ExpireReplays
RebuildStatistics
```

La fréquence exacte doit être configurable.

# 45. Sécurité applicative

Exigences minimales :

- HTTPS ;
- mots de passe hashés via les mécanismes Symfony ;
- sessions sécurisées ;
- CSRF sur formulaires Twig sensibles ;
- authentification API adaptée au mobile ;
- rate limiting sur connexion, reset et endpoints sensibles ;
- validation côté serveur ;
- upload sécurisé ;
- contrôle de permission par ressource ;
- politique de secrets ;
- webhooks signés/vérifiés ;
- audit financier ;
- sauvegardes ;
- séparation DEV/STAGING/PROD.

# 46. Authentification API mobile

Le mécanisme exact doit être choisi au démarrage du chantier mobile.

Contraintes :

- tokens révocables ou durée maîtrisée ;
- renouvellement sécurisé ;
- aucun secret applicatif embarqué considéré comme confidentiel ;
- changement de mot de passe/suspension pris en compte ;
- scopes/permissions contrôlés côté serveur.

# 47. Protection des données

Le système manipule des données professionnelles, personnelles et financières.

À prévoir :

- minimisation des données ;
- classification ;
- contrôle d'accès ;
- durée de conservation ;
- droit d'accès/rectification selon cadre applicable ;
- journalisation des accès sensibles ;
- contrats avec prestataires ;
- procédure d'incident ;
- validation juridique spécifique Côte d'Ivoire avant production.

# 48. Frontoffice Twig - architecture de présentation

Structure indicative :

```text
Presenter/Frontoffice/
+-- Controller/
+-- ViewModel/
+-- Form/        (seulement si nécessaire)
+-- Twig/        (ou templates globaux selon convention projet)
```

Le projet peut centraliser les fichiers Twig sous `templates/` tout en conservant une organisation par contexte :

```text
templates/
+-- frontoffice/
|   +-- content/
|   +-- learning/
|   +-- shared/
+-- member/
|   +-- contribution/
|   +-- learning/
|   +-- identity/
+-- backoffice/
    +-- content/
    +-- contribution/
    +-- learning/
    +-- system/
```

# 49. Design system Web

Composants de base :

- header ;
- navigation ;
- footer ;
- breadcrumbs ;
- card contenu ;
- card formation ;
- badge gratuit/payant ;
- badge Live ;
- liste d'événements ;
- pagination ;
- formulaire ;
- alertes ;
- modal ;
- tableaux ;
- empty states ;
- skeleton/loading ;
- player wrapper ;
- timeline/progression.

Manus doit produire et documenter les états : normal, hover, focus, loading, empty, error, disabled.

# 50. SEO et partage public

Pour les pages publiques :

- `title` et meta description ;
- Open Graph ;
- URLs canoniques ;
- sitemap ;
- robots selon environnement ;
- données structurées pertinentes si retenues ;
- redirections propres lors d'un changement de slug important.

Les espaces membre et Backoffice ne doivent pas être indexés.

# 51. Accessibilité

Objectifs :

- navigation clavier ;
- focus visible ;
- contrastes suffisants ;
- labels de formulaires ;
- structure de titres cohérente ;
- textes alternatifs ;
- lecteurs vidéo accessibles dans la limite du fournisseur ;
- messages d'erreur compréhensibles.

# 52. Performance

Mesures :

- pagination ;
- requêtes Doctrine surveillées ;
- éviter N+1 ;
- cache HTTP/read models lorsque pertinent ;
- thumbnails optimisés ;
- lazy loading images ;
- documents servis via stockage adapté ;
- index SQL sur recherches fréquentes ;
- métriques avant optimisation prématurée.

# 53. Observabilité

Prévoir :

- logs structurés ;
- correlation ID ;
- erreurs applicatives ;
- supervision workers Messenger ;
- métriques de queue ;
- métriques de paiement ;
- suivi des synchronisations YouTube ;
- alertes sur échecs répétés.

# 54. Gestion des erreurs externes

Les intégrations externes doivent distinguer :

```text
TEMPORARY_FAILURE
PERMANENT_FAILURE
AUTHENTICATION_FAILURE
RATE_LIMITED
INVALID_REQUEST
```

Les erreurs temporaires peuvent être retentées. Les erreurs fonctionnelles permanentes doivent produire une erreur exploitable par l'administrateur.

# 55. Tests

## 55.1 Unit tests

Priorité aux règles métier :

- publication ;
- accès formation ;
- activation Enrollment ;
- complétion ;
- émission certificat ;
- allocation de cotisation ;
- transitions de statut ;
- Money.

## 55.2 Integration tests

- Doctrine repositories ;
- YouTube adapter avec fake/stub ;
- prestataire de paiement avec sandbox/fake ;
- stockage ;
- Messenger.

## 55.3 Functional tests

- pages publiques ;
- permissions ;
- backoffice ;
- parcours inscription ;
- achat ;
- accès Live ;
- document privé.

## 55.4 Contract/API tests

Valider :

- schémas ;
- codes HTTP ;
- erreurs ;
- pagination ;
- permissions.

# 56. Stratégie de doubles de test

Créer des ports/interfaces pour les services externes afin de fournir :

```text
FakeVideoProvider
FakePaymentProvider
InMemoryNotificationSender
InMemoryClock / TestClock
```

Éviter de faire dépendre les tests métier du réseau.

# 57. CI/CD

Pipeline minimal :

```text
Checkout
  |
  +-- Composer validate/install
  +-- Lint PHP/Twig/YAML
  +-- Coding standard
  +-- Static analysis
  +-- Unit tests
  +-- Integration tests
  +-- Functional tests
  +-- Security/dependency checks
  |
  v
Build artifact/container
  |
  v
Deploy staging
  |
  v
Smoke tests
  |
  v
Deploy production (controlled)
```

# 58. Environnements

```text
LOCAL
TEST
STAGING
PRODUCTION
```

Chaque environnement possède :

- base distincte ;
- secrets distincts ;
- credentials YouTube distincts si possible ;
- prestataire de paiement en sandbox hors production ;
- stockage distinct ;
- email sécurisé pour éviter les envois accidentels.

# 59. Migrations et données

Règles :

- toute modification de schéma via migration versionnée ;
- migration testée sur copie réaliste ;
- scripts d'import idempotents autant que possible ;
- aucun changement manuel non documenté en production ;
- seeders/fixtures réservés aux environnements adaptés.

# 60. Stratégie de migration des cotisations

Si un système existe :

1. inventaire des sources ;
2. dictionnaire de données ;
3. mapping source -> nouveau modèle ;
4. détection des doublons ;
5. reprise à blanc ;
6. rapport d'écarts ;
7. validation métier ;
8. reprise finale ;
9. procédure de rollback ;
10. conservation des traces d'import.

# 61. Backoffice - règles UX

Le Backoffice doit :

- afficher les permissions utiles ;
- distinguer brouillon/publication ;
- demander confirmation pour actions irréversibles ;
- fournir une recherche efficace ;
- permettre filtres et exports lorsque validés ;
- afficher l'historique d'actions sensibles ;
- éviter de mélanger gestion éditoriale et financière sur un même écran.

# 62. Espace membre - règles UX

Le Dashboard doit prioriser :

- prochains Lives ;
- formations en cours ;
- progression ;
- situation de cotisation si autorisée ;
- notifications utiles ;
- certificats récents.

Ne pas afficher une donnée financière sensible dans un widget si l'utilisateur n'a pas la permission de consulter le détail correspondant.

# 63. Navigation Frontoffice

Proposition de navigation :

```text
Accueil
Actualités
Evénements
Médiathèque
Formations
Lives
A propos

[Connexion]
[Espace membre] si connecté
```

La navigation exacte relève du travail UX et peut être ajustée sans modifier le domaine.

# 64. Architecture de dossiers projet

```text
project/
+-- assets/
+-- bin/
+-- config/
+-- migrations/
+-- public/
+-- src/
|   +-- IdentityContext/
|   +-- ContentContext/
|   +-- ContributionContext/
|   +-- LearningContext/
|   +-- PaymentContext/
|   +-- NotificationContext/
|   +-- MediaContext/
|   +-- AuditContext/
|   +-- SharedContext/
+-- templates/
|   +-- frontoffice/
|   +-- member/
|   +-- backoffice/
+-- tests/
|   +-- Unit/
|   +-- Integration/
|   +-- Functional/
+-- docs/
|   +-- product/
|   +-- architecture/
|   +-- modules/
|   +-- api/
|   +-- adr/
|   +-- agents/
+-- translations/
```

# 65. ADR - Architecture Decision Records

Les décisions importantes doivent produire un ADR court.

Exemples :

```text
ADR-001 Modular Monolith
ADR-002 Training COURSE and LIVE
ADR-003 YouTube as initial video provider
ADR-004 Public Twig frontoffice
ADR-005 Payment idempotency strategy
ADR-006 Contribution integration strategy
ADR-007 Mobile authentication strategy
```

Structure :

```text
Context
Decision
Alternatives
Consequences
Status
Date
```

# 66. Documentation vivante

Arborescence recommandée :

```text
docs/
+-- product/
|   +-- vision.md
|   +-- glossary.md
|   +-- roadmap.md
+-- architecture/
|   +-- context-map.md
|   +-- security.md
|   +-- permissions.md
|   +-- data-model.md
+-- modules/
|   +-- content.md
|   +-- contribution.md
|   +-- learning.md
|   +-- payment.md
+-- api/
|   +-- conventions.md
|   +-- openapi.yaml
+-- adr/
+-- agents/
    +-- codex.md
    +-- manus.md
    +-- workflow.md
```

# 67. Epics de développement

## FOUNDATION

- `FND-001` Initialiser Symfony et conventions projet
- `FND-002` Configurer environnements
- `FND-003` Configurer Doctrine/Migrations
- `FND-004` Configurer Messenger et failure transport
- `FND-005` Logging/correlation ID
- `FND-006` Pipeline CI
- `FND-007` Structure documentation/ADR

## IDENTITY

- `IDN-001` Modèle User
- `IDN-002` Authentification Web
- `IDN-003` Gestion rôles/permissions
- `IDN-004` Voters/Policies
- `IDN-005` Espace profil
- `IDN-006` Backoffice utilisateurs

## CONTENT

- `CNT-001` Tronc commun publication
- `CNT-002` Actualités
- `CNT-003` Evénements
- `CNT-004` Vidéos éditoriales
- `CNT-005` Galeries
- `CNT-006` Documents publics
- `CNT-007` Frontoffice contenu
- `CNT-008` Backoffice contenu
- `CNT-009` Recherche publique

## MEDIA

- `MED-001` Media aggregate/model
- `MED-002` Upload image
- `MED-003` Upload document
- `MED-004` Storage privé
- `MED-005` Livraison sécurisée

## LEARNING

- `LRN-001` Training aggregate
- `LRN-002` Catalogue public
- `LRN-003` Training COURSE
- `LRN-004` Modules
- `LRN-005` VideoContent YouTube
- `LRN-006` DocumentContent
- `LRN-007` Enrollment
- `LRN-008` Espace Mes formations
- `LRN-009` Learning player Twig
- `LRN-010` Progression
- `LRN-011` Training LIVE
- `LRN-012` Calendrier Lives public
- `LRN-013` YouTube provider abstraction
- `LRN-014` YouTube provisioning
- `LRN-015` YouTube synchronization
- `LRN-016` Join Live access policy
- `LRN-017` Replay
- `LRN-018` Quiz MVP
- `LRN-019` Certificats

## PAYMENT

- `PAY-001` Order aggregate
- `PAY-002` PaymentTransaction
- `PAY-003` Provider interface
- `PAY-004` Première intégration provider
- `PAY-005` Webhook idempotent
- `PAY-006` Payment -> Enrollment
- `PAY-007` Historique utilisateur
- `PAY-008` Backoffice transactions

## CONTRIBUTION

- `CTR-000` Audit métier et système existant
- `CTR-001` Dictionnaire de données
- `CTR-002` Modèle final validé
- `CTR-003` Import/synchronisation POC
- `CTR-004` ContributionAssessment
- `CTR-005` Espace situation avocat
- `CTR-006` Gestion paiements/allocations
- `CTR-007` Reçus
- `CTR-008` Backoffice cotisations
- `CTR-009` Rapports

## NOTIFICATION

- `NTF-001` Notification model
- `NTF-002` Email adapter
- `NTF-003` In-app notifications
- `NTF-004` Live reminders
- `NTF-005` Contribution reminders

## MOBILE

- `MOB-001` Contrats API/OpenAPI
- `MOB-002` Auth mobile
- `MOB-003` Actualités/événements
- `MOB-004` E-learning
- `MOB-005` Cotisations
- `MOB-006` Notifications

# 68. Format standard d'un ticket Codex

Chaque ticket doit contenir :

```text
ID
Titre
Contexte métier
Bounded Context
Objectif
Préconditions
Règles métier
Entrées
Sorties
Permissions
Erreurs attendues
Evénements émis
Dépendances
Critères d'acceptation
Tests obligatoires
Hors périmètre
Documentation à mettre à jour
```

Exemple :

```text
LRN-007 - Créer Enrollment

Context: LearningContext

Règles:
- user et training doivent exister
- une inscription active identique ne doit pas être dupliquée
- FREE_REGISTRATION ne requiert pas Payment
- ORDER requiert un paiement validé via le workflow prévu

Acceptance:
- command et handler existent
- règles unit testées
- repository interface définie
- persistence implémentée
- événement EnrollmentActivated émis si applicable
- aucune logique dans le controller
```

# 69. Règles Codex

Codex doit :

1. lire le ticket et les documents du contexte ;
2. identifier l'agrégat propriétaire de la règle ;
3. respecter les namespaces existants ;
4. privilégier les modifications locales et cohérentes ;
5. écrire ou mettre à jour les tests ;
6. exécuter les validations disponibles ;
7. documenter une décision structurante via ADR ;
8. signaler une ambiguïté métier plutôt que l'inventer.

Codex ne doit pas :

- déplacer des classes entre contextes sans décision ;
- créer un « God service » ;
- injecter EntityManager directement partout ;
- mettre les règles d'autorisation dans Twig ;
- appeler YouTube directement depuis un contrôleur ;
- appeler un prestataire de paiement directement depuis le domaine ;
- modifier le schéma sans migration ;
- contourner les tests parce qu'une fonctionnalité « marche manuellement ».

# 70. Règles Manus AI

Manus doit travailler à partir :

- des parcours validés ;
- du design system ;
- des ViewModels/API disponibles ;
- de la matrice des permissions.

Livrables attendus :

- sitemap ;
- parcours ;
- wireframes ;
- maquettes ;
- responsive ;
- états d'erreur/vide/chargement ;
- composants documentés.

Manus ne doit pas inventer des règles financières, d'accès ou de formation qui contredisent le domaine.

# 71. Workflow Codex + Manus

```text
Product requirement
       |
       v
Ticket + UX flow
       |
       +-------------------+
       |                   |
       v                   v
    Codex                Manus
 Domain/API           UI/UX contract
       |                   |
       +---------+---------+
                 |
                 v
            Integration
                 |
                 v
               Tests
                 |
                 v
              Review
```

Le contrat entre les deux doit être explicite : routes, ViewModels, API, erreurs et permissions.

# 72. Definition of Ready

Un ticket est prêt si :

- le besoin est compris ;
- le Bounded Context est connu ;
- les règles métier sont écrites ;
- les permissions sont définies ;
- les dépendances sont connues ;
- les critères d'acceptation sont testables ;
- les ambiguïtés importantes sont résolues ou explicitement hors périmètre.

# 73. Definition of Done

Un ticket est terminé si :

- le code respecte l'architecture ;
- les migrations sont incluses si nécessaire ;
- les tests requis passent ;
- les permissions sont testées ;
- les erreurs sont gérées ;
- la documentation est mise à jour ;
- le code ne contient pas de secret ;
- l'interface est responsive si elle est concernée ;
- la fonctionnalité fonctionne sur staging ;
- aucun TODO critique non suivi ne subsiste.

# 74. Ordre recommandé de réalisation

```text
0. Cadrage / audit cotisations
          |
1. Fondation technique
          |
2. Identity + permissions
          |
3. Media + Frontoffice shell
          |
4. ContentContext
          |
5. Learning COURSE
          |
6. Enrollment + espace membre
          |
7. PaymentContext
          |
8. Learning LIVE + YouTube
          |
9. Progression / Quiz / Certificats
          |
10. ContributionContext validé
          |
11. API stabilisée
          |
12. Flutter
```

Content et Learning peuvent être parallélisés après stabilisation d'Identity et Media.

# 75. MVP technique proposé

## Inclus

- Frontoffice Twig public ;
- authentification ;
- actualités ;
- événements ;
- vidéos éditoriales ;
- galeries ;
- catalogue public des formations ;
- COURSE avec modules, vidéo YouTube et documents ;
- LIVE avec programmation YouTube ;
- inscriptions gratuites ;
- une intégration paiement ;
- inscriptions payantes ;
- espace membre ;
- progression simple ;
- Backoffice ;
- audit des opérations critiques.

## Différable

- quiz avancés ;
- correction manuelle ;
- recommandations personnalisées ;
- abonnements ;
- multi-licences cabinet ;
- moteur de recherche dédié ;
- push mobile ;
- visioconférence propriétaire ;
- DRM vidéo ;
- analytics avancés.

# 76. Points de décision encore ouverts

Avant implémentation de certains tickets :

- système de cotisations existant ;
- prestataire(s) de paiement ;
- politique de remboursement ;
- durée d'accès aux formations ;
- politique de replay ;
- politique de certificats ;
- validation de l'identité professionnelle ;
- système d'authentification API mobile ;
- infrastructure de stockage ;
- politique de conservation des données ;
- exigences d'hébergement ;
- gouvernance des rôles financiers.

# 77. Risques techniques majeurs

## YouTube

Risque : contrôle limité en dehors de la plateforme.

Réponse : abstraction provider + règles d'accès backend + possibilité de migration future.

## Paiement

Risque : doubles webhooks, statut incertain, interruptions.

Réponse : idempotence + statut local + réconciliation + audit.

## Cotisations

Risque : règles historiques et qualité des données.

Réponse : audit avant modèle final + imports répétables + rapprochement.

## Permissions

Risque : fuite de données financières.

Réponse : permissions fines + tests fonctionnels + audit.

## IA de développement

Risque : dérive architecturale créée par Codex/Manus.

Réponse : tickets bornés + docs + ADR + review + tests.

# 78. Références techniques officielles

Les implémentations doivent être vérifiées au moment du développement avec les documentations officielles, notamment :

- Symfony Security ;
- Symfony Messenger ;
- Doctrine ORM ;
- YouTube Data API / Live Streaming API ;
- YouTube IFrame Player API ;
- documentation du prestataire de paiement retenu.

À la date de cette V3, l'API YouTube Live expose notamment les opérations de création de broadcast/stream, association (`bind`), transition d'état et récupération des broadcasts. Symfony Messenger supporte les transports asynchrones, retries et failure transports ; ces mécanismes doivent être utilisés de manière explicite pour les intégrations externes.

# 79. Résumé des décisions figées

```text
DEC-01  Symfony monolithe modulaire au démarrage
DEC-02  Frontoffice Twig public = surface produit principale
DEC-03  Espace membre Twig séparé du Frontoffice public
DEC-04  Backoffice Twig protégé par permissions fines
DEC-05  API v1 pour Flutter et intégrations
DEC-06  ContentContext séparé du LearningContext
DEC-07  Training = concept e-learning central
DEC-08  COURSE et LIVE = types de Training
DEC-09  Training visibility séparée de Training access type
DEC-10  YouTube = fournisseur, pas autorité d'accès
DEC-11  PaymentContext séparé des règles Learning/Contribution
DEC-12  ContributionContext provisoire jusqu'à audit métier
DEC-13  Documents privés contrôlés côté serveur
DEC-14  Messenger pour intégrations asynchrones
DEC-15  Tests + ADR obligatoires sur décisions structurantes
```

# 80. Conclusion

La plateforme doit être construite comme **un produit unique composé de modules métier autonomes**, et non comme une accumulation de CRUD.

Les quatre surfaces applicatives sont désormais explicitement retenues :

```text
Frontoffice Twig public
Espace membre Twig
Backoffice Twig
API v1 / Flutter
```

Les trois piliers métier sont :

```text
ContentContext
ContributionContext
LearningContext
```

et reposent sur des capacités transversales :

```text
Identity
Payment
Media
Notification
Audit
Search
```

Cette V3 constitue le **contrat d'architecture de départ**. Elle doit être utilisée pour créer les ADR, les contrats API, les tickets et les prompts de développement. Toute divergence majeure introduite par un développeur ou un agent IA doit être justifiée, testée et documentée.

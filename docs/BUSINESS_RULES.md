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

## 6. Payment — principes validés

Lorsqu’il sera introduit, le paiement devra respecter :

- validation serveur ;
- idempotence des webhooks et transitions ;
- traçabilité de la transaction et de son fournisseur ;
- conservation des faits financiers ;
- absence d’accès accordé sur la seule réponse du frontend ;
- absence de manipulation directe de la persistence Learning par Payment.

Le fournisseur, les statuts définitifs et les règles de remboursement ne sont
pas encore arrêtés.

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

## 10. Références

- [`docs/PERMISSIONS.md`](PERMISSIONS.md) ;
- [`docs/ARCHITECTURE.md`](ARCHITECTURE.md) ;
- [`docs/specifications_techniques_plateforme_avocats_ci_v3.md`](specifications_techniques_plateforme_avocats_ci_v3.md).

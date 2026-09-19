# Avocat CI — Roadmap produit et technique

## 1. Convention

- `DONE` : terminé et vérifiable ;
- `IN PROGRESS` : travail engagé mais non terminé ;
- `PLANNED` : séquencé mais non commencé ;
- `DISCOVERY` : dépend d’une découverte ou validation métier ;
- `OUT_OF_SCOPE` : exclu de la phase concernée.

La roadmap ne transforme pas les spécifications V3 en fonctionnalités déjà
livrées. Chaque phase doit produire une verticale cohérente, testée et
documentée.

## 2. Phases

### PHASE 0 — Fondations techniques

**Statut : `DONE`**

- baseline Doctrine et cohérence du schéma ;
- quality gates stabilisés ;
- contextes existants inspectés et conservés ;
- surfaces Web Public, Member et Backoffice formalisées.

Tickets terminés :

```text
FND-001 — Baseline Doctrine                         DONE
FND-002 — Stabiliser les quality gates               DONE
FND-003 — Surfaces Public / Member / Backoffice     DONE
```

### PHASE 1 — Documentation et fondation Web

**Statut : `IN PROGRESS`**

- DOC-001 — formaliser la documentation produit ;
- WEB-001 — construire le shell Frontoffice Avocat CI ;
- stabiliser navigation, layouts, responsive et accessibilité ;
- conserver `/espace` comme surface Member minimale tant qu’aucun domaine
  membre n’est livré.

### PHASE 2 — ContentContext

**Statut : `IN PROGRESS`**

La fondation Content Backoffice est terminée et vérifiable (`DONE`) pour les
actualités, événements, vidéos éditoriales, galeries, couvertures et documents.
Les parcours Content Frontoffice restent différés dans la phase Manus et ne
doivent pas retarder le démarrage de Learning.

Tickets immédiats :

```text
CNT-001 — Actualités                         IMPLEMENTED
CNT-001A — Catégories et tags génériques      IMPLEMENTED
CNT-002 — Événements                         IMPLEMENTED
CNT-003 — Vidéos éditoriales                  IMPLEMENTED — Backoffice uniquement
```

La suite de la phase concerne les parcours Frontoffice et les décisions de
visibilité publique ; ces éléments restent hors de la présente fondation
Backoffice.

### PHASE 3 — Learning COURSE et inscriptions

**Statut : `IN PROGRESS`**

Ticket de départ :

```text
LRN-001 — Training COURSE
```

Ordre indicatif : `Training`, modules, contenus pédagogiques, catalogue,
`Enrollment`, puis lecture protégée et intégration à la Member Area.

### PHASE 4 — Payment

**Statut : `PLANNED`**

La phase définira `Order`, `PaymentTransaction`, le fournisseur, les webhooks,
l’idempotence et les contrats d’intégration. Aucun fournisseur n’est choisi par
DOC-001.

### PHASE 5 — Learning LIVE et YouTube

**Statut : `PLANNED`**

Le `LIVE` sera introduit comme un `Training` autonome avec `LiveDetails`,
programmation, synchronisation et contrôle d’accès serveur. YouTube restera un
fournisseur et non une autorité métier.

### PHASE 6 — Progression, quiz et certificats

**Statut : `PLANNED`**

Cette phase couvrira `LearningProgress`, les quiz et les certificats après
validation de leurs règles précises.

### PHASE 7 — Contribution

**Statut : `DISCOVERY`**

La découverte métier doit précéder toute implémentation. Elle devra clarifier
les périodes, montants, exemptions, pénalités, paiements partiels, arriérés,
reçus, régularisations, imports et source de vérité.

### PHASE 8 — API v1

**Statut : `PLANNED`**

L’API ne sera stabilisée qu’après les contrats métier Web. Elle pourra servir au
mobile et à des intégrations ciblées, avec autorisation et validation côté
backend.

### PHASE 9 — Flutter

**Statut : `PLANNED`**

Flutter viendra après stabilisation de l’API v1 et des règles métier qu’elle
expose.

## 3. Ordre immédiat

```text
DOC-001  Documentation produit                     IN PROGRESS
WEB-001  Shell Frontoffice                         PLANNED
CNT-001  Actualités                                IMPLEMENTED
CNT-002  Événements                                IMPLEMENTED — Backoffice uniquement
CNT-003  Vidéos éditoriales                         IMPLEMENTED — Backoffice uniquement
CNT-004  Galeries photos + Media public minimal     IMPLEMENTED — Backoffice uniquement
CNT-004A Couvertures et galeries liées News/Event   IMPLEMENTED — Backoffice uniquement
CNT-005  Documents / Publications sécurisés          IMPLEMENTED — Backoffice + téléchargements contrôlés
Content Backoffice foundation                     DONE
Content Frontoffice                              OUT_OF_SCOPE — différé phase Manus
LRN-001  Training COURSE                           DONE — Backoffice uniquement
LRN-002  Structure COURSE                          DONE — modules/leçons Backoffice
LRN-003  Contenu pédagogique des leçons             DONE — éditeur, YouTube et ressources privées Backoffice
```

Les tickets suivants ne doivent pas être anticipés dans une phase précédente.

## 4. Dépendances et garde-fous

- Identity et permissions précèdent les accès métier ;
- Content peut être livré avant Learning ;
- Learning `COURSE` précède les parcours complexes et les certificats ;
- Payment doit être séparé des décisions Learning et Contribution ;
- Contribution ne démarre qu’après découverte du système existant ;
- API et Flutter attendent des contrats métier stabilisés ;
- chaque verticale doit ajouter ses tests, permissions et documentation
  spécifiques.

## 5. Hors périmètre de la roadmap immédiate

- création simultanée de tous les contextes cibles ;
- migration globale d’architecture ;
- règles financières ou de cotisation inventées ;
- API mobile avant besoin et contrat validés ;
- remplacement des contextes existants stables.

## 6. Références

- [`docs/PROJECT_CONTEXT.md`](PROJECT_CONTEXT.md) ;
- [`docs/DOMAIN.md`](DOMAIN.md) ;
- [`docs/specifications_techniques_plateforme_avocats_ci_v3.md`](specifications_techniques_plateforme_avocats_ci_v3.md).

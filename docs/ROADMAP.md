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

Fondation logging :

```text
LOG-REV-001 — Audit complet de LogContext          DONE
LOG-001      — Sécuriser la fondation de logging   DONE
LOG-002      — Fondation Business Audit Trail     DONE
LOG-003      — Intégrations Content/Learning/Pay. DONE
```

`LOG-001` sécurise uniquement le logging technique existant. Il n’ajoute pas
d’événements d’audit métier et ne transforme pas `Logs` en journal append-only.
`LOG-002` fournit `AuditEntry` et sa consultation Backoffice read-only.
`LOG-003` connecte les événements métier Content, Learning, Payment et
Identity via des subscribers best-effort, sans import direct de `LogContext`
dans les producteurs.

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
CNT-006 — Pages statiques                     IMPLEMENTED — Backoffice uniquement ; Frontoffice différé
CNT-006A — Couverture facultative des pages  IMPLEMENTED — Backoffice uniquement
```

La suite de la phase concerne les parcours Frontoffice et les décisions de
visibilité publique ; ces éléments restent hors de la présente fondation
Backoffice.

### PHASE 3 — Learning Backoffice foundation

**Statut : `COMPLETE`**

Ticket de départ :

```text
LRN-001 — Training COURSE
```

`Training`, modules, contenus pédagogiques, `Enrollment`, taxonomies et LIVE
sont livrés pour le Backoffice, avec les contrôles d’accès membre nécessaires.
Le catalogue public et le parcours Learner Frontoffice restent différés.

### PHASE 4 — Payment

**Statut : `IN PROGRESS`**

PAY-001 livre la fondation PaymentContext, les offres de formation, le workflow
Fake et l’administration Backoffice. PAY-002 ajoute KkiaPay, sa vérification
serveur, le webhook et le checkout membre minimal.

Tickets :

```text
PAY-001 — PaymentContext + workflow Fake              DONE
PAY-002 — KkiaPay + vérification serveur + Webhook    DONE
PAY-002B — SDK PHP officiel KkiaPay                   DONE — adaptateur SDK et mapping DTO
PAY-003 — fulfillment Payment → Learning              DONE — état durable, retry et réconciliation
PAY-002A — certification Sandbox et hardening         PENDING — recette externe requise
```

### PHASE 5 — Learning LIVE et YouTube

**Statut : `IMPLEMENTED`**

Le `LIVE` est un `Training` autonome avec `LiveTrainingDetails`, programmation
et contrôle d’accès serveur. Le `joinUrl` HTTPS est administré dans Avocat CI ;
une référence YouTube facultative peut être intégrée dans l’espace membre via
`youtube-nocookie`, sans appel à l’API YouTube. Aucun fournisseur externe ne
devient l’autorité des droits d’accès.

### PHASE 6 — Progression, quiz et certificats

**Statut : `IN PROGRESS`**

LRN-006 est livré pour la progression COURSE côté domaine, membre et
Backoffice. Les quiz, certificats et le lecteur apprenant restent à planifier
après validation de leurs règles précises.

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
DOC-001  Documentation produit                     DONE
CORE-FIX-001 Consolidation backend                 CURRENT
CNT-006  Static Pages                               DONE — Backoffice uniquement
CNT-006A Optional static page cover                 DONE — Backoffice uniquement
WEB-001  Shell Frontoffice                         PLANNED
CNT-001  Actualités                                IMPLEMENTED — Backoffice + première verticale publique FO-002
FO-002   Actualités publiques dynamiques             DONE — liste, détail, pagination, catégories, tags et covers
CNT-002  Événements                                IMPLEMENTED — Backoffice uniquement
CNT-003  Vidéos éditoriales                         IMPLEMENTED — Backoffice uniquement
CNT-004  Galeries photos + Media public minimal     IMPLEMENTED — Backoffice uniquement
CNT-004A Couvertures et galeries liées News/Event   IMPLEMENTED — Backoffice uniquement
CNT-005  Documents / Publications sécurisés          IMPLEMENTED — Backoffice + téléchargements contrôlés
Content Backoffice foundation                     DONE
Content Frontoffice                              PARTIAL — Actualités et Événements publiques, recherche globale et catalogue Formations livrés ; autres verticales différées
LRN-001  Training COURSE                           DONE — Backoffice uniquement
LRN-002  Structure COURSE                          DONE — modules/leçons Backoffice
LRN-003  Contenu pédagogique des leçons             DONE — éditeur, YouTube et ressources privées Backoffice
LRN-004  Enrollment et contrôle d’accès              DONE — inscription gratuite, attribution/révocation et téléchargement membre protégé
LRN-004A Catégories et tags des formations           DONE — taxonomies Learning, associations et filtres Backoffice
LRN-005  LIVE                                        DONE — détails de session, publication type-aware et accès membre sécurisé
LRN-REV-001A Learning Backoffice foundation           COMPLETE — hardening des accès, CSRF, suppressions bulk et intégrité interne
LRN-006  Progression apprenant                        DONE — progression COURSE, endpoints membre et synthèse Backoffice
LRN-006A Éligibilité apprenante ROLE_AVOCAT             DONE — policy centralisée sur Enrollment, accès et Payment
LRN-007  YouTube Live dans l’espace avocat             DONE — player YouTube protégé, sans API YouTube
FO-004   Catalogue public des formations               DONE — liste, filtres, détails COURSE/LIVE et confidentialité LIVE
FO-005   Recherche globale Frontoffice                  DONE — modal accessible, autocomplete public et résultats Actualités/Événements/Formations
PAY-001  PaymentContext + workflow Fake                DONE — offres, snapshots, idempotence et accès Learning
PAY-003  Fulfillment Payment → Learning                DONE — CONFIRMED/PENDING, retry et commande de réconciliation
NOT-REV-001 Notification/Event audit                   DONE — pipeline synchrone et risques documentés
NOT-001  Notifications Learning/Payment                DONE — événements in-app scalaires et déduplication persistée
NOT-REV-002 Validation du pipeline Notification        DONE — replays, KkiaPay et best-effort vérifiés
Learner Frontoffice                              DEFERRED — parcours apprenant public, inscription et paiement
Public Training catalogue                       DONE — livré par FO-004 ; player et accès consommateur restent protégés
```

La spécification technique V3 conserve un backlog cible historique dont la
numérotation Learning est différente (`LRN-007` Enrollment, `LRN-008` espace
Mes formations, `LRN-011` Training LIVE). Cette numérotation ne décrit pas la
séquence d’implémentation suivie dans ce dépôt ; la roadmap et les exigences
produit ci-dessus font foi pour l’historique réel, où YouTube Live est `LRN-007`.

`CORE-FIX-001` traite les dettes techniques restantes de la revue backend :
lecture batch des références Learning depuis Payment, échappement des titres
UI et synchronisation documentaire. Une fois ses quality gates validés,
`CNT-006 Static Pages` est livré côté Backoffice ; sa lecture publique finale
reste différée avec la conception Frontoffice.

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

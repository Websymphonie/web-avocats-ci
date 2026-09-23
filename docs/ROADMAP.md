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
Les parcours Content Frontoffice sont livrés progressivement : Actualités,
Événements et Vidéos éditoriales sont publics ; les galeries restent différées.

Tickets immédiats :

```text
CNT-001 — Actualités                         IMPLEMENTED
CNT-001A — Catégories et tags génériques      IMPLEMENTED
CNT-002 — Événements                         IMPLEMENTED
CNT-003 — Vidéos éditoriales                  IMPLEMENTED — Backoffice + Frontoffice `/videos`
CNT-007 — Catégories éditoriales des vidéos   IMPLEMENTED — CRUD Backoffice + association et filtre préparé
CNT-006 — Pages statiques                     IMPLEMENTED — Backoffice + détail public `/informations/{slug}`
CNT-006A — Couverture facultative des pages  IMPLEMENTED — Backoffice + rendu public
CNT-006B — Groupes éditoriaux des Pages     IMPLEMENTED — classification + sidebar publique dynamique
CNT-006C — Ordre éditorial des Pages         IMPLEMENTED — ordre Backoffice + tri public
CNT-PROFESSION-001 — Devenir avocat          IN PROGRESS — Page `PROFESSION` préparée en DRAFT ; validation éditoriale requise avant publication
CNT-LEGAL-001 — Migration Vie privée & Mentions légales IMPLEMENTED — Pages LEGAL existantes publiées et contenu source migré ; anciennes URL WordPress à évaluer dans une passe SEO distincte
CNT-008 — Pages institutionnelles BAR        IMPLEMENTED — routes canoniques `/le-barreau/{slug}`
CNT-BAR-002 — Migration Présentation & Historique IMPLEMENTED — contenus de démonstration publiés avec couverture historique locale
CNT-BAR-003 — Migration Bâtonnier & Conseil actuels IMPLEMENTED — 1 titulaire courant, 19 membres ordonnés, portraits locaux et Pages BAR publiées
CNT-009 — Donnée structurée du Bâtonnier      IMPLEMENTED — historique Content + bloc public sur `le-batonnier`
CNT-010 — Conseil de l’Ordre structuré         IMPLEMENTED — historique Content + bloc public sur `conseil-de-l-ordre`
CNT-CARPA-001 — Préparer Page institutionnelle CARPA IMPLEMENTED — état initial : fixture BAR DRAFT à l’ordre 60 et fallback hub ; état remplacé par CNT-BAR-005
CNT-LBC-001 — Page institutionnelle LBC/FT/FP IMPLEMENTED — Page BAR publiée à l’URL dédiée `/lbc-ft-fp`, textes et liens issus de la source officielle sans validation de leur actualité juridique
CNT-ASSIST-001 — Bureau d’Assistance aux Victimes de Violence Domestique IMPLEMENTED — Page Content publique sans PageGroup spécialisé, route dédiée et numéros issus des publications du Barreau
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
CNT-006  Static Pages                               DONE — Backoffice + détail public
CNT-006A Optional static page cover                 DONE — Backoffice + rendu public
CNT-006B Editorial page groups                      DONE — classification + sidebar publique dynamique
CNT-006C Editorial page ordering                    DONE — ordre Backoffice + tri public
CNT-PROFESSION-001 Devenir avocat                    IN PROGRESS — Page `PROFESSION` en DRAFT, validation éditoriale requise avant publication
WEB-001  Shell Frontoffice                         PLANNED
CNT-001  Actualités                                IMPLEMENTED — Backoffice + première verticale publique FO-002
FO-002   Actualités publiques dynamiques             DONE — liste, détail, pagination, catégories, tags et covers
CNT-002  Événements                                IMPLEMENTED — Backoffice uniquement
CNT-003  Vidéos éditoriales                         IMPLEMENTED — Backoffice uniquement
CNT-004  Galeries photos + Media public minimal     IMPLEMENTED — Backoffice uniquement
CNT-004A Couvertures et galeries liées News/Event   IMPLEMENTED — Backoffice uniquement
CNT-005  Documents / Publications sécurisés          IMPLEMENTED — Backoffice + téléchargements contrôlés
Content Backoffice foundation                     DONE
Content Frontoffice                              PARTIAL — Actualités, Événements, Vidéos, recherche globale et catalogue Formations livrés ; galeries et autres verticales différées
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
MEM-FUND-001 Ressources du Fonds de Solidarité membre  DONE — documents LAWYER classés par tag et téléchargement contrôlé
FO-004   Catalogue public des formations               DONE — liste, filtres, détails COURSE/LIVE et confidentialité LIVE
FO-005   Recherche globale Frontoffice                  DONE — modal accessible, autocomplete public et résultats Actualités/Événements/Formations/Pages publiées
FO-009   Contact public                                READY FOR VISUAL REVIEW — formulaire sécurisé, archivage et notification email
FO-010   Hub public « Le Barreau »                      READY FOR VISUAL REVIEW — listing éditorial des Pages BAR et routes canoniques
CNT-BAR-004 — Fonds de Solidarité & ressources avocat    IMPLEMENTED — Page BAR publiée, trois ressources LAWYER chargées depuis les fixtures locales
CNT-BAR-005 — CARPA & ressources validées                IMPLEMENTED — Page BAR publiée à l’ordre 60, règlement général du Barreau distinct en PUBLIC ; ressources CARPA dédiées non retrouvées exclues
FO-FUND-002 Surface publique du Fonds de Solidarité      DONE — Page BAR publiée, contenu CARE et CTA vers l’espace avocat
BKO-CONTACT-001 Messages de contact Backoffice                DONE — liste paginée, statuts, détail immuable et retry manuel audité
DIR-001 Fondation de publication de l’annuaire Avocats/Cabinets DONE — données, opt-in, critères V1 et fixtures synthétiques ; aucun listing public ni import réel
DIR-002 Profils annuaire sans compte                     DONE — displayName, User facultatif, provenance UUID privée, statut UNKNOWN et téléphones Cabinet multiples ; validation schéma isolé et revue navigateur complétées avec DATA-DIR-005
DATA-DIR-005 Import local idempotent de l’annuaire       DONE — dry-run par défaut, écriture explicite testée sur MySQL isolé ; 605 profils / 377 Cabinets, sans User ni portraits ; 7 partiels, 10 asymétries documentées
DATA-DIR-006 Import des portraits historiques              DONE WITH SOURCE GAPS — 579 récupérés/associés, 24 URL en 404, 2 sans source ; environ 120 Mio de sources UUID-nommées locales, Media public, second passage sans doublons en base MySQL isolée
FO-DIR-001 Annuaire public Avocats                  DONE — recherche nom/cabinet/localité, liste paginée et liens vers fiches publiques
FO-DIR-002 Fiches publiques Avocat & Cabinet        DONE — UUID publics, éligibilité DIR-001, coordonnées professionnelles et membres publiables
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
`CNT-006 Static Pages` est livré côté Backoffice et expose désormais le détail
public des Pages publiées. Depuis CNT-008, les Pages `BAR` utilisent les URLs
canoniques `/le-barreau/{slug}` et les autres Pages restent sous
`/informations/{slug}`, sans listing générique.

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

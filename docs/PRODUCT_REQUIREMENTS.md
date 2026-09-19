# Avocat CI — Exigences produit

## 1. Convention de statut

| Statut | Signification |
|---|---|
| `IMPLEMENTED` | livré dans le dépôt et vérifiable |
| `PLANNED` | validé comme capacité cible, non livré |
| `DISCOVERY` | nécessite une découverte ou une décision métier |
| `OUT_OF_SCOPE` | exclu du périmètre courant |

Une exigence `PLANNED` ou `DISCOVERY` ne doit pas être présentée comme une
fonctionnalité disponible dans l’interface.

## 2. Principes communs

- l’autorisation est vérifiée côté backend ;
- la validation métier ne repose pas sur Twig ou JavaScript ;
- les données financières et historiques sont traçables ;
- les écrans suivent les surfaces Public, Member et Backoffice ;
- les libellés destinés aux utilisateurs sont en français ;
- les exigences responsive et d’accessibilité s’appliquent à toute nouvelle
  surface ;
- les conventions UI sont définies dans
  [`docs/UI_UX_GUIDELINES.md`](UI_UX_GUIDELINES.md) et ne sont pas dupliquées
  ici.

## 3. Surface Public Frontoffice

| ID | Exigence | Statut |
|---|---|---|
| PUB-001 | afficher une page d’accueil publique | `IMPLEMENTED` |
| PUB-002 | consulter les actualités publiées | `PLANNED` |
| PUB-003 | consulter les événements et leurs détails | `PLANNED` |
| PUB-004 | consulter les vidéos éditoriales et galeries | `PLANNED` |
| PUB-005 | consulter les documents explicitement publics | `PLANNED` |
| PUB-006 | parcourir le catalogue des formations | `PLANNED` |
| PUB-007 | consulter une fiche de formation ou de Live publiée | `PLANNED` |
| PUB-008 | rechercher les contenus publics | `PLANNED` |
| PUB-009 | fournir une base SEO, responsive et accessible | `PLANNED` |

Les exigences publiques ne donnent pas automatiquement accès aux contenus
protégés. Une fiche publique peut présenter une formation payante sans exposer
son lecteur ou ses documents.

## 4. Surface Member Area

| ID | Exigence | Statut |
|---|---|---|
| MEM-001 | protéger `/espace` par authentification | `IMPLEMENTED` |
| MEM-002 | fournir une page d’accueil Member minimale | `IMPLEMENTED` |
| MEM-003 | consulter et modifier son profil selon autorisation | `PLANNED` |
| MEM-004 | consulter ses formations et Lives | `PLANNED` |
| MEM-005 | consulter sa progression | `PLANNED` |
| MEM-006 | consulter ses certificats | `PLANNED` |
| MEM-007 | consulter ses cotisations et paiements | `DISCOVERY` |
| MEM-008 | consulter ses notifications | `PLANNED` |

La Member Area est une surface de présentation et ne justifie pas la création
d’un `MemberContext`.

## 5. Surface Backoffice

| ID | Exigence | Statut |
|---|---|---|
| ADM-001 | conserver l’entrée `/admin` et le dashboard existant | `IMPLEMENTED` |
| ADM-002 | réserver l’accès du dashboard aux rôles administratifs autorisés | `IMPLEMENTED` |
| ADM-003 | gérer les contenus éditoriaux dans leur contexte propriétaire | `PLANNED` |
| ADM-004 | gérer les formations et Lives dans `LearningContext` | `PLANNED` |
| ADM-005 | gérer les cotisations dans `ContributionContext` après découverte | `DISCOVERY` |
| ADM-006 | gérer les utilisateurs et permissions via `IdentityContext` | `IMPLEMENTED` |
| ADM-007 | consulter les logs et capacités de notification existantes | `IMPLEMENTED` |
| ADM-008 | administrer les paiements dans `PaymentContext` | `PLANNED` |

Une route `/admin` ne change pas la propriété métier d’un écran. Les futurs
contrôleurs, formulaires, use cases et modèles restent dans leur contexte
propriétaire.

## 6. Content

| ID | Exigence | Statut |
|---|---|---|
| CNT-001 | publier et administrer les actualités | `PLANNED` |
| CNT-002 | publier et administrer les événements | `PLANNED` |
| CNT-003 | publier les vidéos éditoriales | `PLANNED` |
| CNT-004 | publier les galeries photos | `PLANNED` |
| CNT-005 | publier les documents et annonces | `PLANNED` |
| CNT-006 | séparer les contenus éditoriaux des contenus pédagogiques | `IMPLEMENTED` — principe |

## 7. Learning

| ID | Exigence | Statut |
|---|---|---|
| LRN-001 | introduire `Training` comme concept central | `PLANNED` |
| LRN-002 | distinguer les types `COURSE` et `LIVE` | `PLANNED` — décision validée |
| LRN-003 | administrer une `COURSE` et ses modules | `PLANNED` |
| LRN-004 | gérer les contenus pédagogiques | `PLANNED` |
| LRN-005 | gérer `Enrollment` et l’accès pédagogique | `PLANNED` |
| LRN-006 | suivre la progression | `PLANNED` |
| LRN-007 | gérer quiz et certificats | `PLANNED` |
| LRN-008 | gérer les détails d’un `LIVE` autonome | `PLANNED` |
| LRN-009 | intégrer YouTube comme fournisseur, sans lui déléguer l’autorisation | `PLANNED` |

Les règles de durée d’accès, de certificat, de score, de replay et de
remboursement restent à décider.

## 8. Contribution

| ID | Exigence | Statut |
|---|---|---|
| CTR-001 | documenter les périodes et types de cotisation | `DISCOVERY` |
| CTR-002 | valider les montants, exemptions et pénalités | `DISCOVERY` |
| CTR-003 | valider paiements partiels, arriérés et régularisations | `DISCOVERY` |
| CTR-004 | valider la production des reçus | `DISCOVERY` |
| CTR-005 | identifier la source de vérité et les imports | `DISCOVERY` |
| CTR-006 | implémenter la gestion des cotisations après validation | `PLANNED` |

Aucun calcul ou statut définitif n’est introduit par ce document.

## 9. Payment

| ID | Exigence | Statut |
|---|---|---|
| PAY-001 | représenter une commande et ses lignes historisées | `PLANNED` |
| PAY-002 | tracer les transactions et statuts | `PLANNED` |
| PAY-003 | traiter les webhooks de manière idempotente | `PLANNED` |
| PAY-004 | ne jamais accorder un accès sur une réponse frontend seule | `IMPLEMENTED` — principe |
| PAY-005 | transmettre un paiement confirmé au contexte propriétaire | `PLANNED` |
| PAY-006 | choisir un fournisseur après validation du besoin | `DISCOVERY` |

## 10. API et mobile

| ID | Exigence | Statut |
|---|---|---|
| API-001 | stabiliser des contrats API JSON versionnés | `PLANNED` |
| API-002 | permettre une future consommation Flutter | `PLANNED` |
| API-003 | conserver les règles métier et permissions côté backend | `PLANNED` — principe |

La création d’API n’est pas requise pour les fonctionnalités documentaires ou
Web actuelles.

## 11. Hors périmètre courant

- création immédiate de `ContentContext`, `LearningContext`, `PaymentContext` ou
  `ContributionContext` ;
- création d’une API, d’un système JWT/OAuth mobile ou de Flutter ;
- choix d’un fournisseur de paiement ;
- gel du modèle métier des cotisations ;
- ajout de règles de certification, remboursement, replay ou expiration non
  validées.

## 12. Références

- [`docs/PROJECT_CONTEXT.md`](PROJECT_CONTEXT.md) ;
- [`docs/DOMAIN.md`](DOMAIN.md) ;
- [`docs/ARCHITECTURE.md`](ARCHITECTURE.md) ;
- [`docs/PERMISSIONS.md`](PERMISSIONS.md) ;
- [`docs/specifications_techniques_plateforme_avocats_ci_v3.md`](specifications_techniques_plateforme_avocats_ci_v3.md).

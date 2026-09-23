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
| PUB-002 | consulter les actualités publiées | `IMPLEMENTED` — liste publique, détail, pagination, catégories et tags |
| PUB-003 | consulter les événements et leurs détails | `PLANNED` |
| PUB-004 | consulter les vidéos éditoriales publiées | `IMPLEMENTED` — liste, détail YouTube sécurisé, miniatures et accès depuis la homepage ; les galeries publiques restent planifiées |
| PUB-005 | consulter les documents explicitement publics | `PLANNED` |
| PUB-006 | parcourir le catalogue des formations | `IMPLEMENTED` — catalogue public, catégories, filtres COURSE/LIVE et accès |
| PUB-007 | consulter une fiche de formation ou de Live publiée | `IMPLEMENTED` — détails publics, covers et données LIVE non sensibles |
| PUB-008 | rechercher les contenus publics | `IMPLEMENTED` — recherche globale Actualités, Événements, Formations et Pages publiées, autocomplete et accès contrôlé aux seuls contenus publiés |
| PUB-009 | fournir une base SEO, responsive et accessible | `PLANNED` |

Les exigences publiques ne donnent pas automatiquement accès aux contenus
protégés. Une fiche publique peut présenter une formation payante sans exposer
son lecteur ou ses documents.

## 4. Surface Member Area

| ID | Exigence | Statut |
|---|---|---|
| MEM-001 | fournir le dashboard et le shell Member protégés | `IMPLEMENTED` |
| MEM-002 | consulter ses formations et sa progression synthétique | `IMPLEMENTED` — liste membre COURSE/LIVE et filtres |
| MEM-003 | utiliser le player COURSE membre protégé | `IMPLEMENTED` |
| MEM-004 | accéder à ses expériences LIVE protégées | `IMPLEMENTED` — détail LIVE, contrôle d’accès et lien sécurisé |
| MEM-005 | consulter ses paiements de formation | `IMPLEMENTED` — historique membre en lecture seule et états de fulfillment |
| MEM-FUND-001 | consulter les ressources du Fonds de Solidarité | `IMPLEMENTED` — publications LAWYER paginées et classées avec le tag Content dédié |
| MEM-006 | consulter une vue détaillée de sa progression | `PLANNED` |
| MEM-007 | consulter ses certificats | `PLANNED` |

La Member Area est une surface de présentation et ne justifie pas la création
d’un `MemberContext`. Le profil membre et les notifications membre sont déjà
livrés dans le socle Member ; ils ne constituent pas des tickets futurs
distincts dans cette séquence.

## 5. Surface Backoffice

| ID | Exigence | Statut |
|---|---|---|
| ADM-001 | conserver l’entrée `/admin` et le dashboard existant | `IMPLEMENTED` |
| ADM-002 | réserver l’accès du dashboard aux rôles administratifs autorisés | `IMPLEMENTED` |
| ADM-003 | gérer les contenus éditoriaux dans leur contexte propriétaire | `IMPLEMENTED` — News |
| ADM-004 | gérer les formations et Lives dans `LearningContext` | `IMPLEMENTED` — Training COURSE et LIVE Backoffice |
| ADM-005 | gérer les cotisations dans `ContributionContext` après découverte | `DISCOVERY` |
| ADM-006 | gérer les utilisateurs et permissions via `IdentityContext` | `IMPLEMENTED` |
| ADM-007 | consulter les logs et capacités de notification existantes | `IMPLEMENTED` |
| ADM-008 | administrer les paiements dans `PaymentContext` | `IMPLEMENTED` — offres et consultation Backoffice PAY-001 |

Une route `/admin` ne change pas la propriété métier d’un écran. Les futurs
contrôleurs, formulaires, use cases et modèles restent dans leur contexte
propriétaire.

## 6. Content

| ID | Exigence | Statut |
|---|---|---|
| CNT-001 | publier et administrer les actualités | `IMPLEMENTED` — Backoffice uniquement |
| CNT-001A | naviguer dans Content et administrer catégories d’actualités et tags génériques | `IMPLEMENTED` — Backoffice uniquement |
| CNT-002 | publier et administrer les événements | `IMPLEMENTED` — Backoffice uniquement |
| CNT-003 | publier et administrer les vidéos éditoriales | `IMPLEMENTED` — Backoffice uniquement |
| CNT-007 | catégoriser les vidéos éditoriales | `IMPLEMENTED` — CRUD Backoffice, association obligatoire aux nouvelles écritures et filtre public préparé |
| CNT-004 | publier les galeries photos | `IMPLEMENTED` — Backoffice uniquement; Media public minimal, sans médiathèque ni Frontoffice |
| CNT-004A | ajouter couvertures et galeries liées à News/Event | `IMPLEMENTED` — Backoffice uniquement, sans Frontoffice |
| CNT-005 | publier les documents et annonces | `IMPLEMENTED` — Backoffice + téléchargement contrôlé |
| CNT-006 | administrer les pages statiques institutionnelles publiques | `IMPLEMENTED` — Backoffice + détail public `/informations/{slug}`, sans listing |
| CNT-006A | ajouter une couverture facultative aux pages statiques | `IMPLEMENTED` — Backoffice + rendu public réutilisé, sans galerie |
| CNT-006B | classer les Pages statiques par groupes éditoriaux | `IMPLEMENTED` — groupes facultatifs et sidebar publique dynamique |
| CNT-006C | contrôler l’ordre des Pages dans un groupe éditorial | `IMPLEMENTED` — ordre Backoffice et tri public déterministe |
| CNT-BAR-002 | migrer Présentation et Historique du Barreau | `IMPLEMENTED` — contenus de démonstration publiés avec couverture locale de l’Historique |
| CNT-BAR-003 | migrer le Bâtonnier et le Conseil de l’Ordre actuels | `IMPLEMENTED` — Pages BAR publiées, titulaire courant, 19 membres ordonnés et portraits intégrés aux fixtures locales |
| CNT-BAR-004 | migrer le Fonds de Solidarité et ses ressources avocat | `IMPLEMENTED` — Page BAR publiée avec contenu CARE et trois documents LAWYER servis depuis le stockage privé |
| CNT-BAR-005 | migrer la CARPA et ses ressources validées | `IMPLEMENTED` — Page BAR publiée à l’ordre 60 et règlement intérieur général du Barreau distinct publié `PUBLIC`; les ressources CARPA dédiées non retrouvées ne sont pas inventées |
| CNT-009 | administrer et afficher la donnée structurée du Bâtonnier | `IMPLEMENTED` — historique de mandats, portrait facultatif et bloc Page BAR |
| CNT-010 | administrer et afficher la composition structurée du Conseil de l’Ordre | `IMPLEMENTED` — historique des membres, portraits facultatifs et bloc Page BAR |
| BKO-CONTACT-001 | consulter les messages du formulaire Contact | `IMPLEMENTED` — liste paginée, filtres de livraison, détail immuable et retry manuel des échecs |
| DIR-001 | préparer la publication de profils Avocat et Cabinet | `IMPLEMENTED` — UUID profil, visibilité explicite, coordonnées professionnelles, portrait protégé et règle d’éligibilité V1 ; les pages publiques restent à livrer |

## 7. Learning

| ID | Exigence | Statut |
|---|---|---|
| LRN-001 | introduire `Training` comme concept central | `IMPLEMENTED` — fondation + COURSE Backoffice |
| LRN-002 | distinguer les types `COURSE` et `LIVE` | `IMPLEMENTED` — enum, frontière et type LIVE administrable |
| LRN-003 | administrer une `COURSE`, ses modules, leçons et contenu pédagogique | `IMPLEMENTED` — éditeur riche, YouTube externe et ressources privées Backoffice |
| LRN-004 | gérer `Enrollment` et l’accès pédagogique | `IMPLEMENTED` — inscription gratuite, attribution/révocation admin et policy serveur |
| LRN-004A | classer les formations par catégories et tags Learning | `IMPLEMENTED` — taxonomies Backoffice et filtres Training |
| LRN-005 | gérer les détails d’un `LIVE` autonome | `IMPLEMENTED` — Backoffice, publication et accès membre sécurisé |
| LRN-REV-001A | durcir les flux Learning existants | `IMPLEMENTED` — valeurs LIVE stables, invariants de type, accès membre, CSRF, bulk et intégrité interne |
| LRN-006 | suivre la progression | `IMPLEMENTED` — progression COURSE, endpoints membre sécurisés et résumé Backoffice |
| LRN-006A | réserver l’éligibilité apprenante aux avocats actifs | `IMPLEMENTED` — policy Learning réutilisée par Enrollment, accès et Payment |
| LRN-007 | intégrer YouTube Live dans l’espace avocat sans lui déléguer l’autorisation | `IMPLEMENTED` — embed YouTube Live membre, sans API YouTube |
| LRN-008 | gérer quiz et certificats | `PLANNED` |

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
| PAY-001 | initier et confirmer un paiement de formation avec offre et provider abstrait | `IMPLEMENTED` — Fake de test et port provider |
| PAY-002 | intégrer KkiaPay et vérifier les transactions côté serveur | `IMPLEMENTED` — code, checkout et webhook; certification Sandbox externe requise |
| PAY-002B | utiliser le SDK PHP officiel KkiaPay pour la vérification serveur | `IMPLEMENTED` — SDK encapsulé dans l’adaptateur Infrastructure; certification Sandbox réelle toujours externe |
| PAY-003 | fiabiliser le traitement Payment confirmé vers Learning | `IMPLEMENTED` — fulfillment durable, retryable et réconciliable |
| PAY-004 | ne jamais accorder un accès sur une réponse frontend seule | `IMPLEMENTED` — principe |
| PAY-005 | transmettre un paiement confirmé au contexte propriétaire | `IMPLEMENTED` — port vers Learning |
| PAY-006 | choisir un fournisseur après validation du besoin | `IMPLEMENTED` — KkiaPay retenu pour PAY-002 |

## 10. API et mobile

| ID | Exigence | Statut |
|---|---|---|
| API-001 | stabiliser des contrats API JSON versionnés | `PLANNED` |
| API-002 | permettre une future consommation Flutter | `PLANNED` |
| API-003 | conserver les règles métier et permissions côté backend | `PLANNED` — principe |

La création d’API n’est pas requise pour les fonctionnalités documentaires ou
Web actuelles.

## 11. Hors périmètre courant

- implémentation de `ContributionContext` avant la découverte et la validation
  de ses règles métier ;
- création d’une API, d’un système JWT/OAuth mobile ou de Flutter ;
- certification réelle Sandbox KkiaPay et son provisionnement opérationnel ;
- gel du modèle métier des cotisations ;
- ajout de règles de certification, remboursement, replay ou expiration non
  validées.

## 12. Références

- [`docs/PROJECT_CONTEXT.md`](PROJECT_CONTEXT.md) ;
- [`docs/DOMAIN.md`](DOMAIN.md) ;
- [`docs/ARCHITECTURE.md`](ARCHITECTURE.md) ;
- [`docs/PERMISSIONS.md`](PERMISSIONS.md) ;
- [`docs/specifications_techniques_plateforme_avocats_ci_v3.md`](specifications_techniques_plateforme_avocats_ci_v3.md).

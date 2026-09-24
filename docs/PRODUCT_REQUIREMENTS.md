# Avocat CI — Exigences produit

## 1. Convention de statut

| Statut | Signification |
|---|---|
| `IMPLEMENTED` | livré dans le dépôt et vérifiable |
| `PLANNED` | validé comme capacité cible, non livré |
| `DISCOVERY` | nécessite une découverte ou une décision métier |
| `OUT_OF_SCOPE` | exclu du périmètre courant |
| `DRAFT / EDITORIAL VALIDATION REQUIRED` | contenu préparé, non publiable avant validation éditoriale explicite |
| `NOT NEEDED` | besoin produit examiné et non retenu |

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
| PUB-003 | consulter les événements et leurs détails | `IMPLEMENTED` — `/evenements`, détails publiés et navigation depuis la homepage |
| PUB-004 | consulter les vidéos éditoriales publiées | `IMPLEMENTED` — liste, détail YouTube sécurisé, miniatures et accès depuis la homepage ; les galeries publiques restent hors scope actuel |
| PUB-005 | télécharger les documents explicitement publics | `IMPLEMENTED` — téléchargement serveur des publications `PUBLIC`; aucun catalogue général n’est impliqué |
| PUB-006 | parcourir le catalogue des formations | `IMPLEMENTED` — catalogue public, catégories, filtres COURSE/LIVE et accès |
| PUB-007 | consulter une fiche de formation ou de Live publiée | `IMPLEMENTED` — détails publics, covers et données LIVE non sensibles |
| PUB-008 | rechercher les contenus publics | `IMPLEMENTED` — recherche globale Actualités, Événements, Formations et Pages publiées, autocomplete et accès contrôlé aux seuls contenus publiés |
| PUB-009 | fournir une base SEO, responsive et accessible | `PLANNED` |
| FO-009 | contacter institutionnellement le Barreau | `IMPLEMENTED` — formulaire `/contact`, persistance, consentement et suivi de livraison email |

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
| CNT-001 | publier et administrer les actualités | `IMPLEMENTED` — Backoffice et Frontoffice `/actualites` |
| CNT-001A | naviguer dans Content et administrer catégories d’actualités et tags génériques | `IMPLEMENTED` — Backoffice uniquement |
| CNT-002 | publier et administrer les événements | `IMPLEMENTED` — Backoffice et Frontoffice `/evenements` |
| CNT-003 | publier et administrer les vidéos éditoriales | `IMPLEMENTED` — Backoffice et vidéothèque publique `/videos` |
| CNT-007 | catégoriser les vidéos éditoriales | `IMPLEMENTED` — CRUD Backoffice, catégories et filtre public |
| CNT-004 | publier les galeries photos | `IMPLEMENTED` — Backoffice uniquement; Media public minimal, sans médiathèque ni Frontoffice |
| CNT-004A | ajouter couvertures et galeries liées à News/Event | `IMPLEMENTED` — Backoffice uniquement, sans Frontoffice |
| CNT-005 | publier des documents / publications documentaires | `IMPLEMENTED` — Backoffice et téléchargement contrôlé selon le niveau d’accès; pas de modèle Announcement |
| CNT-006 | administrer les pages statiques institutionnelles publiques | `IMPLEMENTED` — Backoffice, détails publics et routes canoniques par groupe (`/le-barreau`, `/carpa`, `/lbc-ft-fp`, `/devenir-avocat`), sans listing générique |
| CNT-006A | ajouter une couverture facultative aux pages statiques | `IMPLEMENTED` — Backoffice + rendu public réutilisé, sans galerie |
| CNT-006B | classer les Pages statiques par groupes éditoriaux | `IMPLEMENTED` — groupes facultatifs et sidebar publique dynamique |
| CNT-006C | contrôler l’ordre des Pages dans un groupe éditorial | `IMPLEMENTED` — ordre Backoffice et tri public déterministe |
| CNT-PROFESSION-001 | préparer la page institutionnelle « Devenir avocat » | `DRAFT / EDITORIAL VALIDATION REQUIRED` — Page `PROFESSION` préparée sous `/devenir-avocat`; conditions historiques à valider avant publication |
| CNT-LEGAL-001 | migrer Vie privée et Mentions légales depuis la source institutionnelle | `IMPLEMENTED` — contenus source dans les Pages LEGAL existantes, publiées sous leurs URL canoniques |
| CNT-BAR-002 | migrer Présentation et Historique du Barreau | `IMPLEMENTED` — contenus de démonstration publiés avec couverture locale de l’Historique |
| CNT-BAR-003 | structurer les personnes institutionnelles et harmoniser leurs cartes | `IMPLEMENTED` — données actuelles du Bâtonnier/Conseil conservées, anciens Bâtonniers structurés sur la Page Historique, 19 membres Conseil réutilisés et cartes harmonisées |
| CNT-BAR-004 | migrer le Fonds de Solidarité et ses ressources avocat | `IMPLEMENTED` — Page BAR publiée avec contenu CARE et trois documents LAWYER servis depuis le stockage privé |
| CNT-BAR-005 | migrer la CARPA et ses ressources validées | `IMPLEMENTED` — Page `CARPA` distincte de `BAR`, hub `/carpa`, fiche `/carpa/presentation`; les ressources CARPA dédiées non retrouvées ne sont pas inventées |
| CNT-LBC-001 | publier la page institutionnelle LBC/FT/FP | `IMPLEMENTED` — Page Content `PageGroup::LBC`, route `/lbc-ft-fp`, ressources externes; aucune actualisation juridique n’est revendiquée et aucun Training n’est créé |
| CNT-ASSIST-001 | publier la page institutionnelle du Bureau d’Assistance aux Victimes de Violence Domestique | `IMPLEMENTED` — Page publique `/assistance-violences-domestiques`, numéros dédiés, sans formulaire métier ni promesse 24/7 ; revue navigateur manuelle non attestée dans la documentation consultée |
| FO-010 | consulter le hub public « Le Barreau » | `IMPLEMENTED` — hub éditorial et fiches BAR canoniques |
| CNT-009 | administrer et afficher la donnée structurée du Bâtonnier | `IMPLEMENTED` — historique de mandats, portrait facultatif et bloc Page BAR |
| CNT-010 | administrer et afficher la composition structurée du Conseil de l’Ordre | `IMPLEMENTED` — historique des membres, portraits facultatifs et bloc Page BAR |
| BKO-CONTACT-001 | consulter les messages du formulaire Contact | `IMPLEMENTED` — liste paginée, filtres de livraison, détail immuable et retry manuel des échecs |
| DIR-001 | préparer la publication de profils Avocat et Cabinet | `IMPLEMENTED` — UUID profil, visibilité explicite, coordonnées professionnelles, portrait protégé et règle d’éligibilité V1 |
| DIR-002 | permettre des profils annuaire sans compte et préserver la provenance | `IMPLEMENTED` — `displayName` autonome, User nullable, statut `UNKNOWN`, UUID source privés et téléphones Cabinet multiples |
| DATA-DIR-005 | importer localement et de façon idempotente l’annuaire historique | `DATASET READY / NOT DEPLOYED` — import explicite dry-run par défaut / `--write`, validé sur MySQL isolé; 605 profils et 377 Cabinets; aucun chargement automatique dev/staging/prod |
| DATA-DIR-006 | récupérer et importer les portraits historiques disponibles | `DATASET READY WITH SOURCE GAPS / NOT DEPLOYED` — 579 portraits, 24 sources 404, 2 profils sans URL; validation MySQL isolée, aucun chargement automatique dev/staging/prod |
| FO-DIR-001 | rechercher et parcourir les avocats publiables | `IMPLEMENTED` — listing `/avocats` filtrable, paginé et lié aux fiches publiques FO-DIR-002 |
| FO-DIR-002 | consulter les fiches publiques avocat et cabinet | `IMPLEMENTED` — `/avocats/{uuid}` et `/cabinets/{uuid}`, critères DIR-001, coordonnées professionnelles et membres éligibles |
| FO-DIR-004 | ouvrir un profil avocat depuis l’annuaire sans perdre le contexte de recherche | `IMPLEMENTED` — clic principal chargé en modale depuis la même projection publique; `/avocats/{uuid}` reste la fiche canonique et le fallback sans JavaScript |

## 7. Learning

| ID | Exigence | Statut |
|---|---|---|
| LRN-001 | introduire `Training` comme concept central | `IMPLEMENTED` — fondation + COURSE Backoffice |
| LRN-002 | distinguer les types `COURSE` et `LIVE` | `IMPLEMENTED` — enum, frontière et type LIVE administrable |
| LRN-003 | administrer une `COURSE`, ses modules, leçons et contenu pédagogique | `IMPLEMENTED` — éditeur riche, YouTube ou Mux signé pour les leçons, ressources privées Backoffice |
| LRN-004 | gérer `Enrollment` et l’accès pédagogique | `IMPLEMENTED` — inscription gratuite, attribution/révocation admin et policy serveur |
| LRN-004A | classer les formations par catégories et tags Learning | `IMPLEMENTED` — taxonomies Backoffice et filtres Training |
| LRN-005 | gérer les détails d’un `LIVE` autonome | `IMPLEMENTED` — Backoffice, publication et accès membre sécurisé |
| LRN-REV-001A | durcir les flux Learning existants | `IMPLEMENTED` — valeurs LIVE stables, invariants de type, accès membre, CSRF, bulk et intégrité interne |
| LRN-006 | suivre la progression | `IMPLEMENTED` — progression COURSE, endpoints membre sécurisés et résumé Backoffice |
| LRN-006A | réserver l’éligibilité apprenante aux avocats actifs | `IMPLEMENTED` — policy Learning réutilisée par Enrollment, accès et Payment |
| LRN-007 | intégrer YouTube Live dans l’espace avocat sans lui déléguer l’autorisation | `IMPLEMENTED` — embed YouTube Live membre, sans API YouTube |
| LRN-VID-001 | neutraliser le modèle vidéo Learning et distinguer diffusion/replay | `IMPLEMENTED` — sources provider-neutral, YouTube actif ; playback MUX/Cloudflare hors scope |
| LRN-VID-002 | lire les vidéos Mux VOD signées dans les leçons COURSE membres | `IMPLEMENTED` — Mux Player Web et jeton serveur après autorisation ; Mux Live et Cloudflare restent hors scope |
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

## 12. Décisions produit closes — NOT NEEDED

| Sujet | Statut | Motif |
|---|---|---|
| CFPA comme système ou surface séparée | `NOT NEEDED` | Les formations et contenus e-learning relèvent de `LearningContext`; aucun `PageGroup::CFPA`, `/cfpa`, hub ou menu dédié. |
| Commissions du Conseil de l’Ordre | `NOT NEEDED` | Le lien historique « Voir Commission » menait aux fiches des membres; aucun modèle, page ou Backoffice Commission dédié. Les commissions d’agence documentées ailleurs sont un domaine distinct. |
| Espace Particuliers | `NOT NEEDED` | Aucun hub `/espace-particuliers` ni groupe/navigation dédiés; les fonctions retenues sont accessibles via les surfaces existantes. |
| Newsletter | `NOT NEEDED` | Aucun besoin produit confirmé; pas d’abonnement ni de workflow Notification dédié. |
| Prise de rendez-vous avocat | `NOT NEEDED` | L’annuaire et les coordonnées professionnelles publiées couvrent la mise en contact; aucune réservation en ligne. |
| Annonces comme domaine éditorial séparé | `NOT NEEDED` | Les communications éditoriales passent par Actualités et les éléments datés par Événements; aucun modèle `Announcement`, route ou Backoffice dédié. |
| Écrire au Bâtonnier / formulaires particuliers génériques | `NOT NEEDED` | Le canal institutionnel retenu est `/contact`; aucun routage dédié ni workflow séparé. |

## 13. Références

- [`docs/PROJECT_CONTEXT.md`](PROJECT_CONTEXT.md) ;
- [`docs/DOMAIN.md`](DOMAIN.md) ;
- [`docs/ARCHITECTURE.md`](ARCHITECTURE.md) ;
- [`docs/PERMISSIONS.md`](PERMISSIONS.md) ;
- [`docs/specifications_techniques_plateforme_avocats_ci_v3.md`](specifications_techniques_plateforme_avocats_ci_v3.md).

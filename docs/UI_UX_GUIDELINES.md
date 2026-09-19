# Avocat CI — UI/UX Guidelines

Ce document définit les conventions permanentes d’interface d’Avocat CI.
Il complète `AGENTS.md` et s’applique à toute nouvelle fonctionnalité.

## Rôle de conception

Chaque fonctionnalité doit être évaluée comme une fonctionnalité métier et
comme une expérience utilisateur. La revue couvre la compréhension utilisateur,
la hiérarchie visuelle, les interactions, le responsive, l’accessibilité, la
prévention des erreurs, les états vides, les formulaires et la cohérence avec
la fondation UI existante.

## Langues

- libellés, messages, erreurs et statuts affichés : français ;
- identifiants de code, classes, méthodes et propriétés : anglais ;
- aucune terminologie technique inutile dans l’interface métier.

## Principes visuels

Les écrans doivent être simples, professionnels, compacts mais lisibles,
responsives, accessibles et orientés métier.

Privilégier :

- une action principale clairement identifiable ;
- peu d’actions visibles simultanément ;
- des libellés explicites ;
- des groupes d’informations cohérents ;
- des espaces blancs utiles ;
- des statuts visibles ;
- des filtres réellement utiles ;
- des erreurs affichées au niveau du champ ;
- des états vides explicites ;
- une confirmation adaptée aux actions destructives.

Éviter les écrans CRUD surchargés, les boutons de même importance visuelle,
les informations dupliquées et les actions critiques cachées.

## Fondation UI

Réutiliser en priorité les composants et conventions existants :

- Twig Components ;
- Tailwind css / Shadcn ;
- messages flash centralisés ;
- layouts, formulaires, pagination et menus d’actions existants.

Ne pas recréer localement un composant déjà centralisé.

## JavaScript : Stimulus obligatoire

Tout comportement JavaScript classique doit être porté par Symfony UX Stimulus,
notamment :

- champs conditionnels ;
- formulaires dynamiques ;
- sélecteurs dépendants ;
- filtres interactifs ;
- onglets et progressive disclosure ;
- confirmations ;
- modales ;
- dropdowns et petits composants réutilisables.

Les balises `<script>` métier inline et les attributs `onclick`, `onchange`,
`onload` ou équivalents sont interdits lorsqu’un controller Stimulus est
approprié. Stimulus améliore l’expérience, mais le serveur, l’Application et le
Domain restent les autorités de validation.

## Symfony UX React

Symfony Forms et Twig sont la solution par défaut. React, via Symfony UX React,
est réservé aux interfaces dont la complexité interactive le justifie :

- dashboards ;
- KPI et statistiques interactives ;
- graphiques dynamiques ;
- filtres analytiques croisés ;
- reporting avec drill-down.

Les calculs métier, l’autorisation et l’intégrité restent côté Symfony. React
ne doit pas être utilisé pour les formulaires simples, le CRUD, les listes
ordinaires ou les détails statiques. Aucune SPA séparée ne doit être introduite
sans décision explicite.

## Modals et pages dédiées

Une modal est choisie selon l’expérience utilisateur, jamais par défaut.

Elle convient aux confirmations courtes, formulaires courts, aperçus et actions
contextuelles secondaires. Une page dédiée est préférable lorsque le formulaire
est long, les conséquences métier importantes, le contexte nécessaire, ou que
la navigation et l’usage mobile en bénéficient.

Une modal interactive doit utiliser Stimulus ou un composant Symfony UX existant.
Ne pas ajouter une bibliothèque frontend externe uniquement pour afficher une
modal.

## Formulaires

Les formulaires utilisent Symfony Forms et Twig. Les champs doivent rester
visibles, lisibles et correctement étiquetés. Les comportements conditionnels
utilisent Stimulus, avec une validation serveur identique lorsque JavaScript est
désactivé ou échoue.

### Affordance et contraste permanents

Tout contrôle de formulaire doit être identifiable avant toute interaction et
rester visuellement distinct de la surface qui l'entoure, en thème clair comme
en thème sombre. Cette règle s'applique aux `input` (texte, nombre, date,
recherche et fichier), `select`, `textarea`, champs Symfony Choice/Entity,
autocomplete/Tom Select, combobox et contrôles composites.

Les champs ne doivent jamais dépendre du seul placeholder, du clic ou du focus
pour révéler leur présence. Les composants Twig, les classes Tailwind et le
thème Symfony partagé doivent fournir au minimum un fond de champ, une bordure
et un texte suffisamment contrastés. Les états
`default`, `hover`, `focus-visible`, `filled`, `disabled`, `readonly` et
`invalid` restent lisibles ; le focus clavier doit être clairement visible et
les erreurs doivent être compréhensibles au niveau du champ. Les champs
désactivés ou en lecture seule restent lisibles et ne doivent pas être rendus
quasi invisibles par une opacité excessive.

Préférer les composants et classes existants (`border-input`, `bg-background`,
`text-foreground`, `text-muted-foreground`, `ring-ring`) en les appliquant au
champ concerné ou dans un composant/form theme partagé. Ne pas ajouter de
sélecteur CSS global ciblant tous les `input`, `select` ou `textarea` : les
checkboxes, radios, fichiers, filtres et composants spécialisés doivent
conserver leurs variantes explicites.

Lorsqu'un composant composite possède une zone de saisie interne, sa bordure et
son fond visibles sont portés par le conteneur du composant afin de conserver la
même affordance qu'un champ natif.

## Responsive et accessibilité

Chaque écran est conçu pour desktop, tablette et mobile étroit.

Selon le contexte, utiliser :

- tables réellement responsives ou cartes ;
- défilement horizontal maîtrisé ;
- filtres empilés ou wrap propre ;
- menus d’actions compacts ;
- informations secondaires repliables.

Exigences minimales :

- HTML sémantique ;
- labels explicites ;
- navigation clavier ;
- focus visible ;
- `aria-label` pour les actions icône seules ;
- statut compréhensible sans dépendre uniquement de la couleur ;
- erreurs de validation claires ;
- modals accessibles lorsqu’elles sont utilisées.

## Tables et actions

Éviter les rangées de boutons répétitifs du type « Voir / Modifier / Payer /
Annuler / Supprimer ». Préférer une action principale et un menu secondaire,
selon les composants existants.

Les actions disponibles dépendent toujours de l’état métier et des permissions.

## Actions destructives et historique financier

Une action destructive doit être visuellement identifiable, confirmée lorsque
nécessaire et accompagnée d’un message métier compréhensible.

Les faits financiers et historiques (`PAID`, `CANCELLED`, `REIMBURSED`,
`REVERSED`) ne doivent pas être supprimés ou modifiés silencieusement. Préférer
des transitions explicites, corrections ou reversals dédiés.

## Référentiels configurables

Un vocabulaire technique stable peut être une enum. Une donnée administrable par
le métier doit être un référentiel persisté. Le choix entre enum, référentiel et
Value Object doit suivre la responsabilité métier et non la seule facilité
technique.

## Revue avant livraison

Avant de déclarer une fonctionnalité terminée, vérifier selon le contexte :

- desktop, tablette et mobile ;
- état vide ;
- erreurs de validation ;
- libellés et valeurs longs ;
- permissions et données désactivées ;
- contraste et distinction des champs en thème clair et sombre ;
- champs remplis, désactivés, en lecture seule et invalides ;
- focus clavier visible sur les contrôles natifs et composites ;
- hiérarchie des actions ;
- actions destructives ;
- cohérence avec la fondation UI.

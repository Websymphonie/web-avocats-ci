# Fixtures de démonstration Content + Learning

## Objet

Le groupe Doctrine `demo` fournit un dataset fictif et francophone pour
explorer le Backoffice, tester la pagination et préparer les futures maquettes
Frontoffice et espace avocat. Il est réservé aux environnements `dev` et `test`.
Il ne contient ni personne réelle, ni texte juridique validé, ni transaction.

Les fixtures sont chargées uniquement sur commande ; aucun chargement n'est
effectué au démarrage de l'application.

## Chargement

Sur une base de développement existante, utiliser explicitement `--append` :

```bash
php bin/console doctrine:fixtures:load --env=dev --group=demo --append
```

Sur une base de test ou une base temporaire neuve, la commande peut être lancée
sans `--append` afin de purger cette base isolée avant chargement :

```bash
APP_ENV=test APP_STORAGE_DIR=/tmp/avocat-fixtures-storage \
php bin/console doctrine:fixtures:load --env=test --group=demo
```

`doctrine:fixtures:load` purge la base par défaut lorsqu'il est utilisé sans
`--append`. Ne jamais lancer cette variante sur la base locale de travail sans
avoir vérifié la cible. DATA-001 n'a pas purgé la base locale du projet.

Le bundle Doctrine Fixtures est activé uniquement en `dev` et `test`. Le groupe
`demo` doit être demandé explicitement ; aucune fixture DATA-001 n'est destinée
à la production.

## Organisation et ordre

Les fixtures sont séparées par responsabilité et utilisent des références
nommées stables :

```text
DemoContentTaxonomyFixtures
DemoLearningTaxonomyFixtures
DemoMediaFixtures
DemoContentFixtures
DemoLearningFixtures
```

Les dépendances chargent les taxonomies et les médias avant les contenus. Les
dates utilisent une seule référence `$now` par fixture puis des offsets fixes.
Les slugs, titres, catégories et associations sont déterministes. Les noms
physiques des fichiers restent générés par le pipeline Media existant, comme
pour un upload réel, et ne servent jamais de référence métier.

## Dataset

| Donnée | Quantité |
|---|---:|
| Catégories News | 6 |
| Tags Content | 10 |
| News | 24 |
| Catégories Event | 6 |
| Events | 18 |
| Catégories Training | 7 |
| Tags Training | 8 |
| Trainings | 14 |
| CourseModules | 21 |
| Lessons | 57 |
| LiveTrainingDetails | 6 |
| Pages | 6 |
| Media covers | 4 |
| TrainingOffer | 5 si la devise XOF active existe |

Répartition principale :

- News : 16 publiées, 6 brouillons, 2 archivées ; certaines ont une cover,
  d'autres non.
- Events : 10 publiés, 4 brouillons, 2 annulés, 2 archivés ; les dates couvrent
  le passé et plusieurs échéances futures.
- Trainings : 8 `COURSE` et 6 `LIVE`, avec des accès `FREE`, `PAID` et
  `RESTRICTED`, ainsi que les visibilités `PUBLIC` et `MEMBER`.
- Trainings : 9 publiés et 5 brouillons ; les COURSE publiés possèdent une
  structure de modules et leçons cohérente.
- LIVE : les modes `ONLINE`, `IN_PERSON` et `HYBRID` sont représentés avec des
  dates futures et des informations fictives valides.
- Pages : 4 publiées et 2 brouillons ; les couvertures restent facultatives.

Les offres PAID utilisent uniquement `XOF` lorsque cette devise existe et est
active dans le catalogue Admin. Aucune devise n'est créée par DATA-001.

## Scénario membre avocat

Le groupe `demo` ajoute également un compte fictif distinct, actif et limité à
`ROLE_AVOCAT`, avec cinq enrollments actifs : trois COURSE et deux LIVE. Les
COURSE couvrent les états non commencé, en cours et terminé ; les LIVE sont
programmés dans le futur et utilisent les données de `DemoLearningFixtures`.

Le mot de passe de ce compte n'est jamais stocké dans le dépôt. Il doit être
fourni uniquement par variable d'environnement lors d'un chargement local :

```bash
APP_DEMO_MEMBER_PASSWORD='valeur-locale-unique' \
php bin/console doctrine:fixtures:load --env=dev --group=demo --append
```

La fixture `DemoMemberLearningFixtures` dépend de `DemoLearningFixtures`, est
idempotente sur l'adresse `avocat.demo@example.test` et ne crée ni paiement,
notification, audit ni donnée de production.

## Assets et stockage

Les assets déjà présents dans le dépôt sont réutilisés :

```text
public/assets/logo.png
public/assets/avatar.png
```

Ils sont copiés temporairement puis envoyés via `MediaUploadServiceInterface`.
Les destinations sont dérivées de `APP_STORAGE_DIR` :

```text
$APP_STORAGE_DIR/public/content/covers
$APP_STORAGE_DIR/public/training/covers
```

Les fichiers temporaires sont supprimés après upload. Le test d'intégrité
utilise une racine de stockage temporaire et la supprime après exécution. Un
échec après stockage est traité par la stratégie de nettoyage du service Media.

## Vérification

Le test fonctionnel isolé suivant crée une base SQLite en mémoire, applique le
schéma, fournit une devise XOF active, charge le groupe `demo` et vérifie les
comptes exacts, les statuts, les associations, l'existence des covers et la
présence des répertoires de stockage :

```bash
vendor/bin/phpunit --no-coverage tests/Functional/DemoFixturesIntegrityTest.php
```

Cette validation n'utilise pas la base locale actuelle du projet.

## Hors scope

DATA-001 ne crée pas de données de production, personnes réelles, textes
juridiques validés, écrans Frontoffice, dashboard membre, fixtures Contribution,
transactions Payment/KkiaPay, enrollments en masse ou API v1. DATA-002 crée
uniquement le scénario membre avocat fictif décrit ci-dessus.

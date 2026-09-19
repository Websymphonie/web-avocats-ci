# Audit technique du projet existant avant intégration du cahier des charges V3

Date de l’audit : 19 septembre 2026  
Périmètre : dépôt `/Users/macbook/Projets/Web/web-avocat`  
Références :

- `AGENTS.md` ;
- `docs/ARCHITECTURE.md` ;
- `docs/PERMISSIONS.md` ;
- `docs/UI_UX_GUIDELINES.md` ;
- `docs/specifications_techniques_plateforme_avocats_ci_v3.md` ;
- code, configuration, templates, tests et base locale disponibles au moment de l’audit.

Cet audit est en mode diagnostic. Aucun contexte métier V3, entité, migration ou dépendance n’a été ajouté.

## 1. Executive Summary

### Conclusion générale

Le projet dispose d’un socle Symfony moderne et réellement structuré autour de contextes, avec une séparation `Application / Domain / Infrastructure / Presenter`, des commandes et requêtes dispatchées par Messenger, une authentification web fonctionnelle, un Backoffice Twig déjà opérationnel et des composants Symfony UX/Tailwind réutilisables.

Ce socle est réutilisable. Il ne justifie pas une reconstruction.

En revanche, l’application actuelle est encore principalement une fondation d’administration technique et d’identité. La V3 métier n’est pas implémentée : aucun modèle détecté pour le contenu éditorial, les formations, les inscriptions, les paiements, les cotisations, les quiz, les certificats, YouTube ou l’API. Le Frontoffice se limite à une page d’accueil et à des composants de layout de démonstration ; l’espace membre n’existe pas.

La stratégie recommandée est donc un monolithe modulaire évolutif : conserver les contextes existants, stabiliser Identity/Security et les migrations, compléter le shell Frontoffice/Member, puis introduire progressivement Content, Learning, Payment et enfin Contribution après découverte métier.

### Points critiques avant tout développement métier

1. Restaurer ou versionner la source des migrations : la base locale annonce 48 migrations exécutées mais `config/migrations/` ne contient aucune migration disponible.
2. Résoudre la dérive des permissions et des tests : 6 failures et 2 errors PHPUnit, dont plusieurs divergences entre tests, code et documentation.
3. Corriger le niveau PHPStan avant de multiplier les modules : 5 erreurs de typage/conception sont déjà présentes.
4. Définir la politique de sécurité des futures routes : la règle finale `^/ -> PUBLIC_ACCESS` rend actuellement le périmètre non-admin public par défaut, ce qui est incompatible avec un futur espace membre protégé.
5. Valider les décisions métier externes : cotisations, prestataire de paiement, durée d’accès, replay, certificats, stockage privé et authentification mobile.

## 2. Méthode et limites

Les vérifications ont porté sur :

- l’arborescence et les namespaces sous `src/`, `config/`, `templates/`, `assets/`, `tests/`, `public/` et `docs/` ;
- `composer.json`, `composer.lock`, `package.json`, `package-lock.json` et la configuration Symfony ;
- les entités Doctrine, repositories, factories, controllers, formulaires, ViewModels, messages et événements ;
- le routeur, le container, les mappings Doctrine, l’état des migrations et le bus Messenger ;
- les tests PHPUnit, PHPStan, les linters Symfony, le build frontend et l’audit Composer.

Limites constatées :

- le répertoire n’est pas un checkout Git : aucun `.git` n’a été trouvé à la racine ou dans le projet ; il n’a donc pas été possible de produire un diff Git fiable ni d’identifier les changements locaux antérieurs ;
- plusieurs fichiers de référence (`PROJECT_CONTEXT.md`, `DOMAIN.md`, `PRODUCT_REQUIREMENTS.md`, `BUSINESS_RULES.md`, `ROADMAP.md`) sont vides ;
- les migrations historiques exécutées en base ne sont pas présentes dans le dépôt ;
- l’analyse porte sur l’environnement local et la base locale, pas sur une infrastructure de staging ou de production.

## 3. Stack technique détectée

| Capacité | État détecté | Observations |
| --- | --- | --- |
| PHP | 8.3.14 | CLI MAMP, extensions nécessaires disponibles ; OPcache, APCu et Xdebug absents dans l’environnement CLI. |
| Symfony | 7.4.17 | LTS, environnement `dev`, debug actif. |
| Doctrine ORM | 3.6.8 | Mapping par attributs, entités réparties par contexte. |
| Doctrine DBAL | 4.5.x-dev | Version de développement verrouillée sur un commit ; risque de reproductibilité et de compatibilité à surveiller. |
| Doctrine Migrations | Bundle 3.7.1 / migrations 3.9.7 | Configuration vers `config/migrations`, mais sources historiques absentes. |
| Symfony Messenger | 7.4.15 | `command.bus`, `query.bus`, `message.bus`, transport `async`, transport d’échec Doctrine. |
| Symfony Security | 7.4.15 | Authenticator custom, provider Doctrine, throttling login/reset, remember-me, voter de permission. |
| Twig | Twig 3.28.0 / TwigBundle 7.4.15 | Twig Components et composants UX utilisés. |
| Forms / Validator | Symfony 7.4.17 | Formulaires Twig/Tailwind et validators dédiés déjà présents. |
| Serializer | Symfony Serializer 7.4.17 + JMS Serializer Bundle 5.5.2 | Présence technique ; aucun contrat API détecté. |
| API | Absente | Aucun préfixe `/api`, aucun API Platform, aucun DTO/contrat API versionné détecté. |
| Tests | PHPUnit 9.6.36 / Symfony PHPUnit Bridge 7.4.17 | 67 tests découverts à l’exécution ; 6 failures et 2 errors. |
| Analyse statique | PHPStan 2.2.9, extensions Symfony/Doctrine | Niveau 6 configuré ; 5 erreurs. |
| Qualité | Linters Symfony + `composer validate` | YAML, Twig, container et schéma Doctrine passent ; `composer validate` signale trois contraintes `*`. |
| Frontend | Webpack Encore 5.3.1, Webpack 5.105.2, TypeScript 5.9.3, Tailwind 4.3.3, Stimulus 3.2.2 | Entrées `app` et `web`, Stimulus, React/Chart.js pour certains écrans. |
| UI | Twig Components, Tailwind, Symfony UX React/Live/Chart/Autocomplete | Le React existe pour des dashboards, sans SPA globale. |
| Stockage fichiers | VichUploader + stockage local public | Mappings `users`, `logo`, `images` sous `public/uploads`; pas de stockage privé objet détecté. |
| Images | LiipImagine + Intervention Image | Cache et transformations locales. |
| Email/notification | Mailer Symfony + Notifier/JoliNotif | DSN par environnement ; pas de flux V3 métier. |
| Cache | Cache filesystem par défaut, pool taggable | Redis seulement commenté dans la configuration. |
| Base locale | MySQL via `DATABASE_URL` | 14 tables observées ; schéma local synchronisé avec les mappings. |
| Docker | Non détecté | Aucun Dockerfile ou compose dans le dépôt. |
| CI/CD | Non détecté | Aucun workflow CI ou pipeline versionné trouvé. |
| Environnement | Local MAMP/PHP CLI, `.env`/`.env.local` | Pas de description versionnée des environnements local/test/staging/production. |

### Dépendances et risques

- `doctrine/dbal` est verrouillé sur une version `4.5.x-dev` ; cette décision doit être documentée et remplacée par une version stable lorsque le projet le permet.
- `kwn/number-to-words`, `nucleos/dompdf-bundle` et `twig/intl-extra` utilisent une contrainte `*`, ce que `composer validate` signale comme non reproductible.
- `google/cloud-vision` est installé mais aucun usage métier n’a été détecté pendant l’audit.
- JMS Serializer est installé mais aucun endpoint ou contrat sérialisé V3 n’est exposé.
- React, React Quill, Recharts et plusieurs UX sont disponibles ; ils ne doivent pas devenir une justification pour transformer l’application Twig en SPA.
- `npm ls` signale comme invalides les quatre dépendances UX installées depuis des chemins `file:` vers `vendor/`, bien que le build frontend réussisse.
- Aucun avis de sécurité Composer n’a été retourné au moment de l’audit.

## 4. Architecture existante réelle

### Contextes détectés

| Contexte | Taille indicative | Responsabilité actuelle observée | Évaluation |
| --- | ---: | --- | --- |
| `AdminContext` | 62 fichiers PHP | Dashboard, réglages, maintenance, images et configuration technique. | Socle Backoffice technique réutilisable. Ne pas y placer automatiquement Content, Learning ou Contribution. |
| `AuthContext` | 37 | Login/logout, activation et reset password, authenticator, contrôles anti-brute-force. | Compatible avec la cible Identity/Auth web, à compléter pour le Member Area. |
| `IdentityContext` | 127 | User, rôles, permissions persistées, profil, changement de mot de passe, activation et voter. | Fondation forte, mais conventions de rôles et tests en dérive. |
| `LogContext` | 41 | Logs techniques et logs d’authentification, consultation et suppression. | Réutilisable pour les logs techniques ; ne remplace pas automatiquement l’audit métier append-only V3. |
| `NotificationContext` | 40 | Notifications in-app, lecture, liste, suppression et service de notification. | Partiel mais directement réutilisable pour la première version. |
| `SharedContext` | 152 | Transactions, événements, cache, fichiers, PDF/Excel/CSV, sécurité partagée, layouts et composants. | Large socle transversal ; attention à ne pas y mettre de nouvelles règles métier. |
| `WebContext` | 4 | Page d’accueil et composants Header/Footer/Nav du site public. | Frontoffice amorcé, mais très incomplet. |

### Organisation des couches

La séparation cible est présente dans la forme :

```text
Presenter -> Application -> Domain
Infrastructure -> Domain / persistence
```

Les contextes existants disposent de `Application/Usecase/Command`, `CommandHandler`, `Query`, `QueryHandler`, de modèles de domaine, repositories, entités Doctrine, factories, controllers, Forms, composants Twig et ViewModels.

Les conventions réelles diffèrent toutefois de la V3 :

- le dossier est `Usecase` et non `UseCase` ;
- les factories sont sous `Infrastructure/Persistence/Factory` et non toujours sous `Infrastructure/Factory` ;
- les templates sont centralisés dans `templates/admin`, `templates/identity`, `templates/logs`, `templates/notifications`, `templates/web`, et non encore dans `frontoffice`, `member`, `backoffice` par domaine métier ;
- les repositories de domaine existants exposent parfois des types Doctrine ou des entités d’infrastructure, ce qui constitue une dette de découplage mais ne justifie pas une migration massive immédiate.

### CQRS réellement utilisé

CQRS est réel dans les fonctionnalités existantes, mais pragmatique :

- les writes existants passent par des `Command` et `CommandHandler` ;
- les lectures existantes passent par des `Query` et `QueryHandler` ;
- les handlers sont tagués automatiquement sur `command.bus`, `query.bus` ou `message.bus` via `config/services.yaml` ;
- les controllers restent généralement des points d’entrée minces ;
- les résultats sont transformés en modèles, ViewModels ou paginations selon les écrans.

Il faut conserver cette convention pour les futurs contextes sans imposer une réécriture des modules stables.

## 5. Structure du repository

### Présent

- `src/` : sept contextes applicatifs ;
- `config/` : packages Symfony, routes, services, mappings Doctrine et Messenger ;
- `templates/` : layouts, composants génériques, Auth, Identity, Admin, Logs, Notifications et une base Web ;
- `assets/` : Stimulus, TypeScript, React pour dashboards, SCSS/Tailwind, entrées `app` et `web` ;
- `tests/` : tests unitaires et applicatifs ciblés, principalement Identity/Auth/Notification/Shared ;
- `public/` : front controller, uploads et build frontend ;
- `docs/` : quelques documents d’architecture/permissions/UI et la spécification V3.

### Absent ou incomplet par rapport à la V3

- pas de `ContentContext`, `LearningContext`, `PaymentContext`, `ContributionContext`, `MediaContext` ou `AuditContext` ;
- pas de `templates/member/`, ni de surface membre dédiée ;
- pas de `templates/frontoffice/content` ou `templates/backoffice/content` ;
- pas de `api/`, de contrats OpenAPI ou de tests de contrat ;
- pas de CI/CD, Docker, documentation d’environnement ou ADR versionnés ;
- pas de dossier de migrations disponible malgré des migrations déjà exécutées en base.

## 6. Modèle métier existant

### Entités Doctrine mappées

Les 12 entités applicatives mappées sont :

| Entité | Namespace / contexte | Responsabilité actuelle | Relations ou particularités |
| --- | --- | --- | --- |
| `User` | `IdentityContext` | Compte authentifiable, rôles, état du compte et sécurité. | UUID v7, id integer de trait, dates, soft delete ; relations vers logs et reset password. |
| `AccountActivation` | `IdentityContext` | Activation de compte. | Référence un User. |
| `RolePermissionsEntity` | `IdentityContext` | Configuration persistée des permissions par rôle. | Table `role_permission_configurations`. |
| `ChangePassword` | `IdentityContext` | Structure de changement de mot de passe. | Fichier présent mais non signalé comme entité mappée dans l’inventaire runtime. |
| `ResetPassword` | `AuthContext` | Jeton et workflow de reset. | One-to-one avec User. |
| `Logs` | `LogContext` | Journal applicatif technique. | Many-to-one avec User. |
| `AuthLog` | `LogContext` | Journal des événements d’authentification. | Flux de logging séparé. |
| `Notifications` | `NotificationContext` | Notification in-app. | Many-to-one optionnel avec User, payload JSON, `readAt`, enums de type/action/access. |
| `Reglages` | `AdminContext` | Réglages système applicatifs. | Consultés par les layouts et services Twig. |
| `Currencies` | `AdminContext` | Référentiel de devises. | Socle technique ; pas encore PaymentContext. |
| `Images` | `AdminContext` | Images de configuration et uploads publics. | VichUploader, types MIME limités, destination locale. |
| `Maintenances` | `AdminContext` | État/configuration de maintenance. | Utilisé par un subscriber de présentation. |

### Concepts V3 non détectés

Aucun `Lawyer` ou profil professionnel dédié, `Content`, `News`, `Event` éditorial, `Training`, `Course`, `Live`, `Enrollment`, `Order`, `PaymentTransaction`, `ContributionAssessment`, `Quiz`, `Certificate`, `Media` générique ou `AuditEntry` n’a été trouvé dans le code, les mappings ou les routes.

### Identifiants et historique

Le projet utilise à la fois un id Doctrine et un UUID v7 sur plusieurs entités, avec des traits partagés de dates et de soft delete. Cette décision est structurante et doit être conservée par défaut pour les futurs agrégats, après validation de la stratégie V3 `UUID/ULID`.

La présence d’un soft delete sur User et d’un historique de logs indique qu’il faut éviter toute suppression physique des données financières ou d’audit lorsque les contextes V3 seront ajoutés.

### Fuite de persistance vers le domaine

L’architecture annoncée veut isoler le domaine, mais l’état actuel contient plusieurs dépendances de ce type :

- interfaces de repositories du domaine typées avec `DoctrineORMQuery` ;
- interfaces de repositories du domaine référençant directement des entités Doctrine ;
- services du domaine utilisant `UploadedFile`, Symfony EventDispatcher/Form ou des classes de présentation ;
- `AuthContext` et `NotificationContext` référencent l’entité `User` du contexte Identity.

Il s’agit d’une dette réelle à ne pas reproduire dans les nouveaux contextes. Elle peut être traitée progressivement au fil des nouvelles verticales, sans refactorisation globale de l’existant.

## 7. Identity & Security

### Capacités déjà présentes

- `User` implémente les interfaces de sécurité Symfony ;
- provider Doctrine par email ;
- authenticator custom et `UserChecker` ;
- login, logout avec CSRF, activation de compte et reset password ;
- throttling login Symfony et rate limiters dédiés au reset ;
- remember-me avec provider Doctrine ;
- rôles `ROLE_SUPER_ADMIN`, `ROLE_ADMIN`, `ROLE_AVOCAT`, `ROLE_USER` dans l’enum ;
- `PermissionEnum`, `RolePermissions`, defaults de permissions, persistence des configurations et `PermissionVoter` ;
- logs d’authentification et revocation du remember-me ;
- forms et composants Twig pour les utilisateurs, rôles et mots de passe.

### Écarts et risques

- les tests échouent sur la matrice de rôles, l’ordre des rôles assignables, les defaults et la fusion de configuration persistée/defaults ;
- `docs/PERMISSIONS.md` conserve une matrice de rôles d’origine immobilière, alors que la spécification V3 attend les profils `Manager`, `Avocat`, `Utilisateur` et des permissions métier encore à définir pour Content/Learning/Contribution ;
- la configuration de sécurité protège explicitement `/admin`, mais sa dernière règle `^/` est `PUBLIC_ACCESS`. Elle ne constitue donc pas un périmètre privé par défaut pour les futures routes membre ;
- la protection CSRF globale est commentée dans `framework.yaml`, même si Symfony Forms et plusieurs actions de suppression utilisent des tokens explicitement ;
- `remember_me.secure: true`, `trusted_proxies: 127.0.0.1` et `SECURE_SCHEME` doivent être validés par environnement avant production ;
- aucune policy d’accès par ressource métier n’existe encore pour les contenus, trainings, documents privés, paiements ou cotisations.

### Réutilisation recommandée

Identity/Auth doit rester la source de vérité de l’identité. Les futurs contextes doivent transporter des identifiants stables et appeler des ports/use cases applicatifs ; ils ne doivent pas partager directement des entités Doctrine entre contextes.

## 8. Frontoffice Twig

### État actuel

Le Frontoffice est amorcé par `WebContext` :

- route publique `GET /` ;
- layout `templates/layouts/web.html.twig` ;
- composants `WebHeader`, `WebFooter`, `WebNavBar` ;
- entrée frontend `web` ;
- composant SEO partagé ;
- composants Tailwind/UX génériques réutilisables.

Mais le header, le footer et la navigation sont encore des placeholders, et la page d’accueil est une démonstration minimale. Aucune page actualité, événement, vidéo, galerie, document, catalogue de formation ou Live n’existe.

### Capacité d’accueil de la V3

Le shell peut accueillir la V3 sans refonte majeure si les pages métier sont ajoutées dans des ViewModels dédiés et si l’organisation des templates évolue progressivement. Il faudra ajouter au minimum : SEO complet, navigation réelle, états vides/erreur, pagination, accessibilité, pages publiques Content et Learning, et distinction explicite entre visibilité publique et accès protégé.

## 9. Espace membre

L’espace membre est absent : aucune route `/member` ou équivalente, aucun template dédié, aucun dashboard membre, aucun catalogue des formations inscrites, aucune progression, aucun certificat et aucune situation de cotisation détectés.

La session et `app.user` existent déjà pour l’application interne. Cette fondation peut être réutilisée, mais l’espace membre doit être conçu comme une surface de présentation séparée du Backoffice, avec routes, navigation, permissions et ViewModels propres.

## 10. Backoffice

### Existant

Le Backoffice est Twig et dispose de :

- layout interne avec header, sidebar, footer, toasts et composants modaux ;
- dashboard ;
- gestion des images, réglages et maintenance ;
- gestion des utilisateurs, rôles et mots de passe ;
- consultation des logs et notifications ;
- formulaires, pagination, filtres, dropdowns et actions protégées par permissions ;
- Stimulus pour les interactions classiques et React/Chart.js pour des dashboards analytiques.

### Compatibilité avec Content / Contribution / Learning

Le shell Backoffice est réutilisable sans refonte majeure. Les domaines métier ne doivent toutefois pas être ajoutés à `AdminContext` par commodité : leurs controllers, forms, use cases, modèles et templates doivent appartenir au contexte métier propriétaire, sous des routes `/admin` si nécessaire.

Le Backoffice actuel est technique et n’offre encore aucun écran opérationnel pour Content, Contribution ou Learning. Les permissions de ces domaines devront être définies avant l’exposition des actions.

## 11. API

Aucune API applicative n’a été détectée :

- aucune route `/api/v1` ;
- aucun contrôleur API ;
- aucun mécanisme d’authentification mobile ;
- aucun format d’erreur ou pagination API ;
- aucun OpenAPI/Swagger ou test contractuel ;
- aucun API Platform.

Le serializer présent est une capacité disponible, pas une API existante. La cible V3 `/api/v1` doit être construite après stabilisation des use cases Twig, afin d’éviter de dupliquer la logique métier. Les contrôleurs API devront produire des DTO/read models et ne jamais exposer les entités Doctrine.

## 12. Doctrine, migrations et données

### État technique

- mapping par attributs, par contexte, dans `Infrastructure/Persistence/Doctrine/Entity` ;
- repositories Doctrine séparés des interfaces/modèles selon les conventions existantes ;
- MySQL configuré par `DATABASE_URL` et `MYSQL_VERSION` ;
- `use_savepoints: true`, soft-deleteable activé, lazy ghost objects activés ;
- UUID v7, dates automatiques et parfois soft delete via traits ;
- upload Vich public local ;
- schéma local validé par `doctrine:schema:validate`.

### Risque migrations — critique

`doctrine:migrations:status` indique 48 migrations exécutées, toutes marquées `migrated, not available`, avec la dernière version `Version20260913110000`. Pourtant, aucune migration PHP n’est disponible sous `config/migrations/` dans le dépôt.

Conséquences :

- impossible de rejouer l’historique sur un environnement neuf de façon fiable ;
- impossible d’auditer précisément l’évolution du schéma ;
- risque élevé de divergence lors de l’introduction des tables V3 ;
- risque de perte de données si une future migration est générée contre une base non reproductible.

Action préalable : récupérer la source canonique des migrations ou produire une baseline explicitement validée, sans modifier la base de production ni supprimer l’historique.

## 13. Messenger, événements et planification

### Existant

- buses `command.bus`, `query.bus` et `message.bus` ;
- commandes/queries existantes enregistrées automatiquement ;
- transport `async` piloté par `MESSENGER_TRANSPORT_DSN` ;
- retry exponentiel, trois tentatives, délai différé ;
- failure transport Doctrine ;
- message marker `AsyncMessage` routé vers `async` ;
- événements/listeners/subscribers utilisés pour activation, logs, notifications et invalidation de cache.

### Manquant pour la V3

Aucun message métier détecté pour YouTube, paiements, rappels de Live, certificats, imports de cotisations ou synchronisation. Symfony Scheduler est installé, mais aucun provider/tâche métier V3 n’a été identifié.

Le socle est suffisant pour les traitements asynchrones futurs. Les messages devront transporter des IDs/valeurs simples, être idempotents et distinguer erreurs temporaires, permanentes, d’authentification et de limitation de débit.

## 14. Tests et qualité

### Vérifications exécutées

| Vérification | Résultat |
| --- | --- |
| `composer validate --no-check-publish` | OK avec avertissements sur trois contraintes `*`. |
| `lint:yaml` | OK, 35 fichiers YAML. |
| `lint:twig` | OK, 205 fichiers Twig. |
| `lint:container` | OK. |
| `doctrine:schema:validate -n` | OK mapping et base locale synchronisés. |
| `vendor/bin/phpunit --no-coverage` | Échec : 67 tests, 141 assertions, 6 failures et 2 errors. |
| `vendor/bin/phpstan analyse` | Échec : 5 erreurs au niveau 6. |
| `composer audit --locked` | Aucun avis de vulnérabilité retourné. |
| `npm run build` | Succès, Webpack compilé. |
| `npm ls --depth=0` | Échec de cohérence : quatre packages UX `file:` sont signalés invalides. |

### Couverture actuelle

Les tests couvrent surtout Auth, Identity, Notification, Admin maintenance et Shared. Il n’existe aucun test métier Content/Learning/Payment/Contribution, aucun test fonctionnel de Frontoffice ou de permissions par ressource V3, et aucun test contractuel API.

### Dette de qualité

PHPStan relève notamment : type iterable absent et appel `array_values` inutile dans `PermissionEnum`, PHPDoc sur variable inexistante dans `UserRepository`, comparaison toujours fausse dans `RoleGroupAccessVoter`, et accès nullsafe inutile dans `CurrencyService`.

Les tests en échec ne sont pas de simples défauts de présentation : ils montrent une dérive entre les règles de permissions documentées, les defaults, l’ordre des rôles et le comportement du query handler. Cette dérive doit être clarifiée avant de prendre Identity comme contrat stable pour les nouveaux modules.

## 15. Infrastructures externes

| Domaine | État actuel | Implication V3 |
| --- | --- | --- |
| Email | Symfony Mailer via DSN | Réutilisable pour activation, reset et notifications ; adapter l’envoi asynchrone si nécessaire. |
| Notifications | Notifier/JoliNotif et in-app | NotificationContext réutilisable ; canaux push/SMS non nécessaires au démarrage. |
| Images | Vich + LiipImagine + stockage local | Réutilisable pour médias publics ; insuffisant seul pour documents privés et stockage objet. |
| Paiement | Aucun provider ni webhook détecté | Décision fournisseur et stratégie d’idempotence à prendre avant PaymentContext. |
| YouTube | Aucun adaptateur ni credential détecté | Abstraction provider à créer dans Learning, sans appel direct depuis un controller. |
| Cache | Filesystem, pools Symfony taggables | Suffisant localement ; Redis à décider selon charge et workers. |
| Queue | Messenger async/failure Doctrine | Fondation présente ; supervision opérationnelle absente. |
| Scheduler | Package installé, pas de tâches métier | À activer uniquement pour un besoin validé. |
| Cloud/storage | Aucun S3/Flysystem/objet détecté | Choix requis pour les fichiers privés, documents et replays. |
| Vision | `google/cloud-vision` installé, usage non trouvé | Dépendance à justifier ou à traiter séparément ; ne pas la propager dans V3 sans besoin confirmé. |

## 16. Comparaison avec la V3

Statuts utilisés : `EXISTE ET COMPATIBLE`, `EXISTE MAIS À ADAPTER`, `PARTIEL`, `ABSENT`, `À NE PAS CRÉER IMMÉDIATEMENT`.

| Domaine V3 | Statut | Justification |
| --- | --- | --- |
| `IdentityContext` | EXISTE MAIS À ADAPTER | User, rôles, permissions, auth et Backoffice existent. Les tests, la matrice documentaire et la stratégie de permissions V3 ne sont pas alignés. |
| `ContentContext` | ABSENT | Aucun agrégat, entité, route, use case, template ou permission éditoriale. À introduire au premier vertical Content réel. |
| `ContributionContext` | À NE PAS CRÉER IMMÉDIATEMENT | Aucun système existant détecté et la V3 le déclare provisoire. Il faut d’abord auditer les règles et sources de cotisations. |
| `LearningContext` | ABSENT | Aucun Training/Course/Live/Enrollment/progression/quiz/certificat. À introduire avec un premier parcours de formation validé. |
| `PaymentContext` | ABSENT | Aucun Order, PaymentTransaction, provider ou webhook. À créer après décision fournisseur et modèle d’accès payant. |
| `NotificationContext` | PARTIEL | In-app et services existent ; il faut les adapter aux événements V3, aux rappels et aux éventuels envois asynchrones. |
| `MediaContext` | PARTIEL | Images publiques et upload local existent dans Admin/Shared. Il manque un modèle média transverse, les fichiers privés et la politique de stockage. |
| `SharedContext` | EXISTE MAIS À ADAPTER | Nombreuses capacités techniques réutilisables ; éviter son extension avec des concepts métier V3. |
| `LogContext` / audit technique | PARTIEL | Logs et auth logs existent ; un audit métier append-only avec acteur, cible, action et métadonnées n’est pas identifié. |
| `AuthContext` | EXISTE ET COMPATIBLE | Login, logout, activation, reset et protections de base sont opérationnels. À compléter seulement selon le besoin Member/API. |
| `WebContext` / Frontoffice | PARTIEL | Shell Twig, layout et route d’accueil existent ; les parcours publics V3 sont absents. |
| Backoffice Twig | PARTIEL | Shell, composants et administration technique existent ; aucun écran Content/Learning/Contribution. |
| Espace membre Twig | ABSENT | Aucune route, template ou use case membre. |
| API v1 | ABSENT | Aucun endpoint ni contrat API. À construire après stabilisation des use cases. |

## 17. Gap Analysis

| Domaine | Existant | Cible V3 | Écart | Impact | Action recommandée |
| --- | --- | --- | --- | --- | --- |
| Fondation | Symfony 7.4, contextes, CQRS pragmatique | Monolithe modulaire documenté | Documentation et CI incomplètes | MOYEN | Conserver le socle, documenter les conventions et ajouter les contrôles CI. |
| Identité | User, Auth, rôles, voter | Identity complet et permissions fines par surface | Tests et matrice non alignés ; profil avocat incomplet | ÉLEVÉ | Stabiliser permissions, rôles canoniques et tests avant les domaines métier. |
| Migrations | Schéma local synchronisé | Migrations versionnées et rejouables | 48 migrations exécutées indisponibles | CRITIQUE | Récupérer la source ou établir une baseline contrôlée avant nouvelle table. |
| Frontoffice | Route `/`, layout et composants | Actualités, événements, médias, formations, Lives, SEO | Shell placeholder, aucun contenu métier | ÉLEVÉ | Étendre WebContext progressivement avec Content/Learning ViewModels. |
| Member Area | Session/User existants | Dashboard, formations, progression, certificats, cotisations | Surface absente | ÉLEVÉ | Créer la surface Member après Identity et un premier use case métier. |
| Backoffice | CRUD technique, Identity, Logs, Notifications | Content, Learning, Contribution administrables | Domaines métier absents | ÉLEVÉ | Ajouter les presenters dans les contextes propriétaires. |
| Content | Aucun modèle | Publication, événements, vidéos, galeries, documents | Écart complet | ÉLEVÉ | Créer ContentContext avec un premier vertical publication. |
| Learning | Aucun modèle | COURSE/LIVE, Enrollment, progression, quiz, certificats | Écart complet et nombreuses décisions métier | CRITIQUE | Commencer par Training COURSE minimal et accès gratuit avant paiement/Live. |
| Payment | Aucun provider | Order, transaction, webhook, idempotence, remboursement | Écart complet | CRITIQUE | Décider provider et construire un port + fake avant l’intégration réelle. |
| Contribution | Aucun modèle ou système détecté | Situation avocat, échéances, allocations, reçus, audit | Règles non découvertes | CRITIQUE | Audit métier et dictionnaire de données avant toute entité définitive. |
| Media | Images publiques et Vich local | Media public/privé, documents sécurisés, stockage évolutif | Pas de modèle transverse ni privé | ÉLEVÉ | Extraire une capacité Media minimale sans casser les uploads existants. |
| Notification | In-app, Notifier/Mailer | événements paiement, Live, replay, certificat, cotisation | Adaptateurs et événements manquants | MOYEN | Réutiliser le modèle actuel, ajouter les types après contrats métier. |
| API | Serializer installé | `/api/v1`, auth mobile, DTO, erreurs, pagination | Écart complet | MOYEN | Après stabilisation des use cases Twig et des permissions. |
| Audit | Logs techniques | audit métier et financier append-only | Sémantique et stockage distincts manquants | ÉLEVÉ | Décider si LogContext évolue ou si un AuditContext est justifié par le premier besoin sensible. |
| Opérations | Async/retry/failure présents | workers supervisés, scheduler et observabilité | supervision/CI absentes | MOYEN | Ajouter des commandes opérables, métriques et pipeline avant production. |

## 18. Architecture cible adaptée à l’existant

```text
src/
├── IdentityContext/       # conserver et stabiliser
├── AuthContext/           # conserver
├── AdminContext/          # administration technique uniquement
├── LogContext/            # logs techniques/auth
├── NotificationContext/   # notifications et adaptateurs
├── SharedContext/         # infrastructure réellement transverse
├── WebContext/            # shell Frontoffice public à étendre
├── ContentContext/        # introduire avec le premier contenu métier
├── LearningContext/       # introduire avec Training COURSE
├── PaymentContext/        # introduire après décision fournisseur
├── MediaContext/          # introduire si la capacité privée/transverse le justifie
├── ContributionContext/   # après audit et validation du métier
└── AuditContext/          # seulement si LogContext ne peut pas porter l’audit métier
```

Règles d’intégration :

- conserver les namespaces existants et le dossier `Usecase` tant qu’aucun chantier de normalisation n’est explicitement décidé ;
- ne pas déplacer les entités existantes ;
- créer les entités Doctrine uniquement dans le contexte propriétaire ;
- faire communiquer les contextes par IDs, événements applicatifs et ports, jamais par partage de modèles Doctrine ;
- utiliser les Query/Command existants comme point d’entrée ;
- servir Twig via ViewModels/read models ;
- réserver `SharedContext` aux services transversaux ;
- garder `AdminContext` comme administration technique et non comme “God context” métier.

## 19. Risques techniques et dette pertinente

### Critiques

- historique des migrations non disponible ;
- absence totale de Payment/Contribution et donc impossibilité de garantir les règles financières ;
- accès public par défaut des routes non-admin, incompatible avec un futur espace membre si aucune règle plus spécifique n’est ajoutée ;
- permissions existantes en dérive entre code, tests et documentation.

### Élevés

- absence de stockage privé et de politique de documents ;
- absence d’audit métier distinct des logs techniques ;
- pas de CI/CD, pas de Docker et pas de supervision des workers ;
- domaine actuel couplé à Doctrine/Symfony dans plusieurs interfaces/services ;
- Frontoffice et Member Area très incomplets par rapport à la cible ;
- absence de tests fonctionnels sur les surfaces et permissions V3.

### Moyens

- contraintes Composer non bornées ;
- DBAL dev verrouillé ;
- dépendances UX `file:` incohérentes avec `npm ls` ;
- documentation produit vide ou partiellement héritée d’un autre projet ;
- cache keys et plusieurs libellés techniques portent encore des traces de l’ancien domaine immobilier (`WsImmobiliers`, KLE Immobilier).

## 20. Plan d’intégration progressif

### Phase 0 — Stabilisation et vérité du dépôt

- restaurer les migrations historiques ou valider une baseline ;
- décider si le projet doit être rattaché à un dépôt Git et fixer la branche de référence ;
- faire passer PHPUnit et PHPStan, en traitant la dérive Identity comme un sujet de décision ;
- clarifier les documents vides et les références immobilières résiduelles ;
- ajouter les contrôles reproductibles de qualité, sans modifier encore le domaine métier.

### Phase 1 — Fondations communes

- formaliser la séparation Frontoffice / Member / Backoffice ;
- expliciter les règles Security et le périmètre privé par défaut ;
- stabiliser les rôles et permissions V3 ;
- décider l’identifiant public (`UUID` ou `ULID`) pour les nouveaux agrégats ;
- formaliser la transaction, l’audit, les événements et l’observabilité ;
- préparer les fakes pour fournisseurs externes.

### Phase 2 — Media minimal et shell Frontoffice

- conserver Vich pour les images existantes ;
- introduire une capacité Media minimale si les documents/galleries l’exigent ;
- compléter Header/Footer/Nav, SEO, pagination, erreurs, accessibilité et responsive ;
- ne pas déplacer les composants génériques existants.

### Phase 3 — ContentContext

- commencer par publication d’actualités et événements ;
- ajouter ensuite vidéos éditoriales, galeries et documents publics ;
- livrer simultanément routes publiques, ViewModels et écrans Backoffice ;
- garder le contenu éditorial séparé du contenu pédagogique Learning.

### Phase 4 — Learning COURSE et Member Area

- introduire `Training` avec type COURSE ;
- créer catalogue public, modules, contenus vidéo/document/text ;
- commencer par inscriptions gratuites et politique d’accès serveur ;
- créer `Enrollment`, espace “Mes formations” et progression simple ;
- ajouter le Backoffice Learning ;
- différer quiz avancés et certificats jusqu’à validation des règles.

### Phase 5 — PaymentContext

- choisir le fournisseur et la devise ;
- construire `Order`, `OrderItem`, `PaymentTransaction`, port provider et fake ;
- implémenter webhook signé/idempotent, réconciliation et audit ;
- connecter `PaymentSucceeded` à l’activation Enrollment sans couplage de persistance.

### Phase 6 — Learning LIVE / YouTube

- définir l’abstraction vidéo ;
- programmer et synchroniser les Lives via Messenger ;
- appliquer une policy backend pour join/replay ;
- ajouter rappels Notification et tâches Scheduler seulement après validation opérationnelle.

### Phase 7 — Progression avancée, quiz, certificats et audit métier

- compléter progression, quiz MVP, certificats ;
- décider la conservation, révocation et preuve des certificats ;
- ajouter l’audit append-only des actions sensibles.

### Phase 8 — ContributionContext

- seulement après découverte des sources, périodes, montants, exemptions, arriérés, paiements partiels, reçus et règles de rapprochement ;
- importer de façon idempotente avec rapport d’écarts et rollback ;
- livrer espace membre et Backoffice Cotisations avec permissions financières testées.

### Phase 9 — API v1 puis mobile

- exposer les mêmes use cases via DTOs, réponses et erreurs documentées ;
- choisir une authentification mobile révocable ;
- ajouter tests de contrat et endpoints public/authentifiés ;
- Flutter ne doit démarrer qu’après stabilisation du contrat API.

## 21. Backlog recommandé

Les tickets ci-dessous sont des tickets de cadrage, pas du code à générer immédiatement.

### FOUNDATION

#### FND-001 — Restaurer la stratégie de migrations

- **Contexte / objectif :** rendre le schéma reproductible avant toute nouvelle table.
- **Situation existante :** 48 versions en base, aucune source disponible dans `config/migrations`.
- **Modification attendue :** récupérer les migrations ou produire une baseline validée et documentée.
- **Modules concernés :** `config/migrations`, Doctrine, documentation d’exploitation.
- **Règles / dépendances :** aucune suppression d’historique ; validation sur copie de base.
- **Risques :** divergence de schéma, perte de données.
- **Acceptation / tests :** installation sur base vide ou baseline contrôlée ; `migrations:status` cohérent ; schema validation.

#### FND-002 — Stabiliser les quality gates

- **Contexte / objectif :** obtenir un état vert avant extension.
- **Situation existante :** 6 failures et 2 errors PHPUnit ; 5 erreurs PHPStan.
- **Modification attendue :** décider les règles Identity, corriger les défauts de type et synchroniser tests/docs.
- **Modules concernés :** Identity, Shared, tests, PHPStan.
- **Risques :** figer une règle de permission erronée.
- **Acceptation / tests :** PHPUnit et PHPStan verts ; aucune suppression de test.

#### FND-003 — Formaliser les surfaces et la sécurité des routes

- **Objectif :** distinguer explicitement public, membre et Backoffice.
- **Situation existante :** `/admin` protégé, règle finale `^/` publique.
- **Modification attendue :** décision documentée et tests fonctionnels d’accès.
- **Modules concernés :** Security, Auth, Web, futurs Member controllers.
- **Acceptation / tests :** route publique, route membre et route admin testées avec utilisateur anonyme/authentifié/insuffisamment autorisé.

#### FND-004 — Introduire CI minimale

- **Objectif :** rendre reproductibles lint, tests, analyse, build et audit dépendances.
- **Situation existante :** aucun pipeline versionné détecté.
- **Modification attendue :** pipeline sans déploiement automatique initial.
- **Acceptation / tests :** pipeline sur environnement propre, artefacts et échecs lisibles.

### IDENTITY / SECURITY

#### IDN-001 — Valider le catalogue de rôles V3

- **Objectif :** aligner roles, permissions persistées, defaults, templates et tests.
- **Situation existante :** `ROLE_SUPER_ADMIN`, `ROLE_ADMIN`, `ROLE_AVOCAT`, `ROLE_USER`, mais documentation héritée et tests contradictoires.
- **Règles métier :** ne pas renommer silencieusement les rôles existants ; traiter les comptes historiques.
- **Acceptation / tests :** matrice validée, defaults déterministes, configurations persistées prioritaires, tests de voter et de formulaire.

#### IDN-002 — Ajouter le profil professionnel minimal

- **Objectif :** permettre au Member/Learning/Contribution d’identifier un avocat sans créer encore les règles de cotisation.
- **Situation existante :** aucun profil `Lawyer` dédié détecté.
- **Dépendances :** décision métier sur vérification d’identité professionnelle.
- **Acceptation / tests :** ownership validé, migrations rejouables, permissions et données minimales documentées.

### MEDIA / FRONTOFFICE

#### MED-001 — Définir le modèle de média public/privé

- **Objectif :** dépasser l’upload d’images de configuration et sécuriser les documents.
- **Situation existante :** Vich local public pour users/logo/images.
- **Modification attendue :** port de stockage, métadonnées, visibilité, validation MIME et livraison autorisée.
- **Risques :** exposition de documents privés, migration des fichiers existants.
- **Acceptation / tests :** téléchargement public et privé contrôlés ; test MIME/taille ; aucun chemin filesystem exposé.

#### WEB-001 — Compléter le shell Frontoffice Twig

- **Objectif :** disposer d’une surface publique prête pour Content/Learning.
- **Situation existante :** layout et composants placeholders.
- **Modification attendue :** navigation, SEO, footer, états vides/erreur, accessibilité et responsive.
- **Acceptation / tests :** route home et composants rendus ; tests fonctionnels de base ; aucun accès au Backoffice via la navigation publique.

### CONTENT

#### CNT-001 — Publication d’actualités et événements

- **Contexte :** premier vertical métier Content.
- **Objectif :** créer, publier, archiver et afficher du contenu public avec slug stable.
- **Cible :** `ContentContext`, `Content`, détails événement, publication status, ViewModels.
- **Dépendances :** FND-001/002/003, WEB-001, MED-001 si couverture média.
- **Acceptation / tests :** brouillon invisible ; publication publique ; slug unique ; pages liste/détail ; permissions Backoffice.

#### CNT-002 — Vidéos éditoriales, galeries et documents

- **Objectif :** étendre Content sans mélanger le contenu Learning.
- **Règles :** visibilité public/membre/restricted ; document privé contrôlé serveur.
- **Acceptation / tests :** provider vidéo comme donnée éditoriale ; galerie ordonnée ; téléchargement autorisé seulement avec permission.

### LEARNING

#### LRN-001 — Training COURSE et catalogue public

- **Objectif :** livrer le premier parcours e-learning minimal.
- **Situation existante :** aucun Learning model.
- **Règles :** `Training` est le concept central ; visibilité et accès sont séparés ; pas de paiement implicite.
- **Dépendances :** Identity, Media, Frontoffice, migrations.
- **Acceptation / tests :** catalogue public ; fiche publique ; module vidéo/document/text ; aucune entité Doctrine exposée à Twig.

#### LRN-002 — Enrollment et espace membre

- **Objectif :** inscrire un membre gratuitement puis afficher ses formations.
- **Règles :** inscription idempotente ; accès pédagogique contrôlé backend ; aucune activation par simple visibilité YouTube.
- **Acceptation / tests :** utilisateur anonyme refusé ; membre autorisé ; doublon empêché ; parcours “Mes formations” et empty state.

#### LRN-003 — Progression, quiz MVP et certificat

- **Objectif :** compléter le parcours après validation métier.
- **Dépendances :** politique de complétion, score, certificats et révocation.
- **Acceptation / tests :** invariants unitaires ; tests fonctionnels d’accès ; émission et consultation contrôlées.

#### LRN-004 — Training LIVE et YouTube

- **Objectif :** programmer, synchroniser et sécuriser l’accès aux Lives/replays.
- **Dépendances :** provider YouTube, Messenger, Scheduler, Notification, policy d’accès.
- **Risques :** quotas, synchronisation externe, fuite de replay.
- **Acceptation / tests :** fake provider ; retries ; idempotence ; join refusé sans droit ; rappel envoyé selon règle validée.

### PAYMENT

#### PAY-001 — Order, transaction et provider abstrait

- **Objectif :** représenter une commande et un paiement sans coupler Learning au fournisseur.
- **Dépendances :** choix prestataire, monnaie, politique de remboursement.
- **Acceptation / tests :** snapshot prix/libellé ; statuts ; fake provider ; transactions cohérentes.

#### PAY-002 — Webhook idempotent et activation d’accès

- **Objectif :** traiter les callbacks sans double paiement ni double inscription.
- **Règles :** signature vérifiée, clé d’idempotence, réconciliation et audit.
- **Acceptation / tests :** même webhook deux fois = un seul effet ; erreur temporaire retryable ; effet métier via événement, sans écriture Learning directe.

### CONTRIBUTION

#### CTR-000 — Audit métier et système des cotisations

- **Objectif :** produire le dictionnaire de données avant modèle définitif.
- **Questions obligatoires :** périodes, natures, montant, exemptions, pénalités, partiels, arriérés, reçus, source de vérité, import/synchronisation/remplacement.
- **Situation existante :** aucun système détecté dans ce dépôt.
- **Acceptation :** règles validées, sources inventoriées, écarts et décision d’intégration documentés.

#### CTR-001 — Import contrôlé des historiques

- **Objectif :** reprendre les données sans réécrire l’historique financier.
- **Dépendances :** CTR-000 et FND-001.
- **Acceptation / tests :** import idempotent, rapport d’erreurs, doublons détectés, rollback et traçabilité.

#### CTR-002 — Situation membre et Backoffice cotisations

- **Objectif :** livrer les écrans et use cases après validation du modèle.
- **Règles :** permissions financières fines, modifications atomiques, auteur/date/justification, pas de suppression physique.
- **Acceptation / tests :** tests métier, permissions et historique financier.

### NOTIFICATION / API

#### NTF-001 — Événements et canaux V3

- **Objectif :** adapter NotificationContext aux inscriptions, paiements, Lives, replays, certificats et cotisations.
- **Situation existante :** in-app existant, Notifier/Mailer présents.
- **Acceptation / tests :** événements non dupliqués, lecture idempotente, envoi asynchrone si requis.

#### API-001 — Contrats API v1

- **Objectif :** exposer les use cases stabilisés sous `/api/v1`.
- **Dépendances :** Identity, permissions, Content/Learning/Payment/Contribution, choix auth mobile.
- **Acceptation / tests :** DTOs, format d’erreur, pagination, tests de contrat et absence d’entités Doctrine dans les réponses.

## 22. ADR réellement nécessaires

Les ADR suivants sont justifiés par une décision durable ; ils ne doivent être créés qu’au moment où la décision est prête :

1. **ADR-001 — Stratégie de restauration/baseline des migrations** : indispensable avant toute évolution du schéma.
2. **ADR-002 — Identifiant public des nouveaux agrégats** : UUID v7 existant versus ULID cible, avec compatibilité des URLs et API.
3. **ADR-003 — Frontoffice, Member Area et Backoffice dans le monolithe Twig** : routing, templates, permissions et ownership.
4. **ADR-004 — Stratégie de permissions V3** : rôles historiques, permissions métier et configurations persistées.
5. **ADR-005 — Media storage public/privé** : conservation de Vich local, stockage objet éventuel, téléchargement sécurisé.
6. **ADR-006 — Provider vidéo YouTube** : abstraction, synchronisation, accès backend et politique de replay.
7. **ADR-007 — Provider de paiement et idempotence** : prestataire, webhooks, réconciliation, remboursements.
8. **ADR-008 — Intégration des cotisations** : source de vérité, import/synchronisation, modèle final et audit.
9. **ADR-009 — Authentification API mobile** : tokens, révocation, renouvellement et prise en compte de la suspension.
10. **ADR-010 — Audit métier** : extension de LogContext ou création d’un AuditContext distinct.

Il n’est pas nécessaire de créer maintenant des ADR artificiels sur chaque entité ou page.

## 23. Questions métier ouvertes

- Quel est le profil juridique/professionnel minimal d’un avocat et qui le valide ?
- Quelles sont les périodes, natures, montants, exemptions, pénalités et règles de régularisation des cotisations ?
- Quelle est la source de vérité actuelle des cotisations et existe-t-il un système externe à reprendre ?
- Quel prestataire de paiement est retenu en Côte d’Ivoire, avec quelles devises, webhooks, remboursements et délais de réconciliation ?
- L’accès à une formation payante expire-t-il ? Les Lives ont-ils un replay et pour quelle durée ?
- Quelles conditions déclenchent la complétion et l’émission/révocation d’un certificat ?
- Quels documents sont publics, réservés aux membres ou privés par ressource ?
- Quel stockage et quelle politique de conservation sont requis pour les documents et médias ?
- Quels rôles financiers et permissions de correction/reversal sont autorisés ?
- Quel mécanisme d’authentification mobile et quels clients consommeront `/api/v1` ?
- Quelle infrastructure cible pour workers Messenger, Scheduler, sauvegardes, observabilité et CI/CD ?

## 24. Résumé final obligatoire

### CE QUI DOIT ÊTRE CONSERVÉ

- le monolithe Symfony 7.4 et les sept contextes existants ;
- `IdentityContext`/`AuthContext` comme fondation de l’identité et de l’authentification ;
- le CQRS pragmatique déjà appliqué aux use cases existants ;
- le shell Twig/UX/Tailwind du Backoffice et les composants partagés ;
- `NotificationContext` pour la première version in-app ;
- `LogContext` pour les logs techniques et d’authentification ;
- Messenger, ses transports, retries et failure transport ;
- les conventions d’UUID, dates, repositories, factories et ViewModels lorsque compatibles.

Justification : ces éléments sont opérationnels, testés en partie et compatibles avec la cible modulaire. Les remplacer maintenant augmenterait le risque sans apporter de capacité métier.

### CE QUI DOIT ÊTRE ADAPTÉ

- la matrice de rôles/permissions et ses tests ;
- les règles Security pour séparer clairement public, membre et Backoffice ;
- le mapping et la source des migrations ;
- le couplage de certains repositories/services de domaine avec Doctrine/Symfony ;
- le shell WebContext, ses placeholders, son SEO et sa navigation ;
- le stockage média pour couvrir les documents privés ;
- la documentation héritée et les traces du domaine immobilier ;
- la cohérence npm des packages UX.

Justification : ces adaptations lèvent des risques transversaux identifiés sans déplacer massivement le code.

### CE QUI DOIT ÊTRE AJOUTÉ

- ContentContext ;
- LearningContext, d’abord avec COURSE puis LIVE ;
- espace membre Twig ;
- PaymentContext après choix du fournisseur ;
- MediaContext seulement si la capacité transverse/privée le justifie ;
- ContributionContext après audit métier ;
- tests fonctionnels et contractuels ;
- CI/CD et observabilité ;
- API v1 après stabilisation des use cases.

Justification : ces capacités sont absentes du dépôt et constituent les écarts fonctionnels majeurs de la V3.

### CE QUI DOIT ÊTRE SUPPRIMÉ OU DÉPRÉCIÉ

- rien ne doit être supprimé pendant cette phase d’audit ;
- les dépendances inutilisées ou contraintes `*` doivent être traitées par tickets séparés après vérification d’usage ;
- les clés/cache names et libellés hérités de l’immobilier peuvent être dépréciés progressivement, avec compatibilité et migration explicites ;
- les logs techniques ne doivent pas être remplacés par un audit métier tant que le nouveau mécanisme n’est pas défini et testé.

Justification : l’absence de Git et de migrations disponibles rend toute suppression particulièrement risquée.

### CE QUI DOIT ÊTRE DÉCIDÉ AVANT DÉVELOPPEMENT

- restauration des migrations et environnement de référence ;
- catalogue de rôles/permissions ;
- profil avocat et validation de l’identité professionnelle ;
- modèle et source des cotisations ;
- fournisseur de paiement et politique de remboursement ;
- stratégie Media/stockage privé ;
- durée d’accès/replay/certificat ;
- authentification API mobile ;
- stratégie d’audit métier ;
- exigences d’hébergement, workers, sauvegardes, observabilité et CI/CD.

Justification : ces décisions affectent le schéma, la sécurité, les finances ou les contrats externes ; elles ne doivent pas être inventées par l’implémentation.

### ORDRE RECOMMANDÉ D’IMPLÉMENTATION

```text
0. Migrations + quality gates + clarification documentaire
1. Identity/Security + surfaces public/member/backoffice
2. Media minimal + shell Frontoffice
3. ContentContext
4. Learning COURSE + Enrollment + espace membre
5. PaymentContext + inscription payante
6. Learning LIVE + YouTube + notifications
7. Progression / quiz MVP / certificats / audit métier
8. Audit et intégration ContributionContext
9. API v1
10. Mobile Flutter
```

## 25. Conclusion

La V3 doit être intégrée au projet existant, pas appliquée comme une migration mécanique de dossiers. Le dépôt possède déjà les bons fondements techniques pour un monolithe modulaire, mais seulement une petite partie des surfaces et domaines métier attendus.

La priorité n’est donc pas de créer tous les contextes annoncés. Elle est de rendre le socle reproductible et cohérent, puis de livrer des verticales complètes et testées : Content, ensuite Learning COURSE et l’espace membre, puis Payment, Learning LIVE, et enfin Contribution après découverte métier. Cette séquence conserve le travail existant, limite les couplages et évite d’inventer des règles financières, d’accès ou de certification.

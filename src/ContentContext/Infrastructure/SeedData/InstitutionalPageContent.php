<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\SeedData;

use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Domain\Enum\PageGroup;
use Websymphonie\ContentContext\Domain\Enum\PageStatus;

/**
 * Canonical source of institutional page copy reused by demo and release bootstraps.
 */
final class InstitutionalPageContent
{
    /**
     * @return list<array{title: string, slug: string, status: PageStatus, group: ?PageGroup, sortOrder: int, contentKey: string}>
     */
    public static function definitions(): array
    {
        return [
            ['title' => 'Présentation du Barreau', 'slug' => 'presentation', 'status' => PageStatus::PUBLISHED, 'group' => PageGroup::BAR, 'sortOrder' => 10, 'contentKey' => 'presentation'],
            ['title' => 'Historique du Barreau', 'slug' => 'historique', 'status' => PageStatus::PUBLISHED, 'group' => PageGroup::BAR, 'sortOrder' => 20, 'contentKey' => 'history'],
            ['title' => 'Le Bâtonnier', 'slug' => 'le-batonnier', 'status' => PageStatus::PUBLISHED, 'group' => PageGroup::BAR, 'sortOrder' => 30, 'contentKey' => 'batonnier'],
            ['title' => 'Conseil de l’Ordre', 'slug' => 'conseil-de-l-ordre', 'status' => PageStatus::PUBLISHED, 'group' => PageGroup::BAR, 'sortOrder' => 40, 'contentKey' => 'council'],
            ['title' => 'Fonds de Solidarité', 'slug' => 'fonds-de-solidarite', 'status' => PageStatus::PUBLISHED, 'group' => PageGroup::BAR, 'sortOrder' => 50, 'contentKey' => 'fund'],
            ['title' => 'Présentation', 'slug' => 'presentation', 'status' => PageStatus::PUBLISHED, 'group' => PageGroup::CARPA, 'sortOrder' => 10, 'contentKey' => 'carpa'],
            ['title' => 'Lutte contre le Blanchiment des Capitaux (LBC/FT/FP)', 'slug' => 'lbc-ft-fp', 'status' => PageStatus::PUBLISHED, 'group' => PageGroup::LBC, 'sortOrder' => 10, 'contentKey' => 'lbc'],
            ['title' => 'Mentions légales', 'slug' => 'mentions-legales', 'status' => PageStatus::PUBLISHED, 'group' => PageGroup::LEGAL, 'sortOrder' => 10, 'contentKey' => 'legalNotice'],
            ['title' => 'Vie privée', 'slug' => 'politique-confidentialite', 'status' => PageStatus::PUBLISHED, 'group' => PageGroup::LEGAL, 'sortOrder' => 20, 'contentKey' => 'privacy'],
            ['title' => 'Bureau d’Assistance aux Victimes de Violence Domestique', 'slug' => 'assistance-violences-domestiques', 'status' => PageStatus::PUBLISHED, 'group' => null, 'sortOrder' => 0, 'contentKey' => 'assistance'],
            ['title' => 'Devenir avocat', 'slug' => 'devenir-avocat', 'status' => PageStatus::DRAFT, 'group' => PageGroup::PROFESSION, 'sortOrder' => 10, 'contentKey' => 'becomeLawyer'],
        ];
    }

    /** @return array{fullName: string, startedAt: string, summary: string} */
    public static function currentBatonnier(): array
    {
        return [
            'fullName' => 'Me Florence LOAN épse MESSAN',
            'startedAt' => '2024-10-02',
            'summary' => 'Première femme à diriger l’Ordre des Avocats de Côte d’Ivoire. Mandat annoncé pour 2024–2027.',
        ];
    }

    /**
     * @return list<array{key: string, title: string, sortOrder: int, entries: list<array{key: string, displayName: string, roleLabel: ?string, periodLabel: ?string, sortOrder: int}>}>
     */
    public static function pagePersonGroups(): array
    {
        $formerBatonnierNames = [
            'Louis Vigouroux', 'Lucien Yapobi', 'Armand Josse', 'Jean Réveillé',
        ];
        $formerBatonnierPeriods = [
            ['René Clarac', '1971–1973'],
            ['Maurice Carlton', '1973–1975'],
            ['Charles Dogue', '1975–1977'],
            ['Camille Adam', '1977–1979'],
            ['Jean Konan-Banny', '1979–1980'],
            ['Maurice Kakou', '1980–1983'],
            ['Julien Mondon-Konan', '1983–1985'],
            ['Emile Dervin', '1985–1987'],
            ['Amadou Fadika', '1987–1989'],
            ['Gabriel Assamoi', '1989–1990'],
            ['Charles Gaby Kouassi', '1991–1993'],
            ['Lucien N’Gouin Claih', '1993–1995'],
            ['Emmanuel Tano K.', '1995–1997'],
            ['Essy N’Gatta', '1997–1999'],
            ['Luc Adje Kacou', '1999–2001'],
            ['Louis Metan', '2000–2003'],
            ['Emmanuel Assi', '2005–2007'],
            ['Mamadou Kone', '2009–2010 ; 2013–2014'],
            ['Joachim Bile-Aka', '2011–2013'],
            ['Marcel Beugre', '2014–2015'],
            ['Thomas Zé N’Dri', '2019–2021'],
            ['Claude Mentenon', '2003–2005 ; 2005–2007 ; 2021–2024'],
        ];

        $entries = [];
        foreach ($formerBatonnierNames as $index => $name) {
            $entries[] = [
                'key' => 'former-' . ($index + 1),
                'displayName' => $name,
                'roleLabel' => null,
                'periodLabel' => null,
                'sortOrder' => $index * 10,
            ];
        }
        foreach ($formerBatonnierPeriods as $index => [$name, $period]) {
            $entries[] = [
                'key' => 'former-' . ($index + count($formerBatonnierNames) + 1),
                'displayName' => $name,
                'roleLabel' => null,
                'periodLabel' => $period,
                'sortOrder' => ($index + count($formerBatonnierNames)) * 10,
            ];
        }

        return [[
            'key' => 'former-batonnier',
            'title' => 'Les anciens Bâtonniers',
            'sortOrder' => 10,
            'entries' => $entries,
        ]];
    }

    /** @return list<array{fullName: string, function: string, portraitReference: string}> */
    public static function councilMembers(): array
    {
        return [
            ['fullName' => 'Maître Florence LOAN épse MESSAN', 'function' => 'Bâtonnier en exercice', 'portraitReference' => 'florence_loan_messan'],
            ['fullName' => 'Maître Arouna OUATTARA', 'function' => 'Secrétaire de l’Ordre', 'portraitReference' => 'arouna_ouattara'],
            ['fullName' => 'Bâtonnier Abbé YAO', 'function' => 'Ancien Bâtonnier 2015-2018', 'portraitReference' => 'abbe_yao'],
            ['fullName' => 'Maître Guizot Bernard TAKORE', 'function' => 'Membre du Conseil de l’Ordre', 'portraitReference' => 'guizot_bernard_takore'],
            ['fullName' => 'Maître A. Geneviève SISSOKO épse DIALLO', 'function' => 'Membre du Conseil de l’Ordre', 'portraitReference' => 'genevieve_sissoko_diallo'],
            ['fullName' => 'Maître Marie Françoise N’CHO-KATCHIRE', 'function' => 'Membre du Conseil de l’Ordre', 'portraitReference' => 'marie_francoise_ncho_katchire'],
            ['fullName' => 'Maître Souhalio Lassoman DIOMANDE', 'function' => 'Membre du Conseil de l’Ordre', 'portraitReference' => 'souhalio_lassoman_diomande'],
            ['fullName' => 'Maître Allard Marie-Ernest SENI', 'function' => 'Membre du Conseil de l’Ordre', 'portraitReference' => 'allard_marie_ernest_seni'],
            ['fullName' => 'Maître Bintou Edith ZAN épse LAGO', 'function' => 'Membre du Conseil de l’Ordre', 'portraitReference' => 'bintou_edith_zan_lago'],
            ['fullName' => 'Maître Josiane Josette KOFFI épse BREDOU', 'function' => 'Membre du Conseil de l’Ordre', 'portraitReference' => 'josiane_josette_koffi_bredou'],
            ['fullName' => 'Maître Aïssata DIABI', 'function' => 'Membre du Conseil de l’Ordre', 'portraitReference' => 'aissata_diabi'],
            ['fullName' => 'Maître Nicolas Tompieu MESSAN', 'function' => 'Membre du Conseil de l’Ordre', 'portraitReference' => 'nicolas_tompieu_messan'],
            ['fullName' => 'Maître N’Dry Claver KOUADIO', 'function' => 'Membre du Conseil de l’Ordre', 'portraitReference' => 'ndry_claver_kouadio'],
            ['fullName' => 'Maître Binta BAKAYOKO-MELSEAUX', 'function' => 'Membre du Conseil de l’Ordre', 'portraitReference' => 'binta_bakayoko_melseaux'],
            ['fullName' => 'Maître Yao Philippe GNIMAVO', 'function' => 'Membre du Conseil de l’Ordre', 'portraitReference' => 'yao_philippe_gnimavo'],
            ['fullName' => 'Maître Roseline KOUAME épse KODJO-AKA', 'function' => 'Membre du Conseil de l’Ordre', 'portraitReference' => 'roseline_kouame_kodjo_aka'],
            ['fullName' => 'Maître Maryse BOHOUSSOU', 'function' => 'Membre du Conseil de l’Ordre', 'portraitReference' => 'maryse_bohoussou'],
            ['fullName' => 'Maître Brice Tézaï MAHAN', 'function' => 'Membre du Conseil de l’Ordre', 'portraitReference' => 'brice_tezai_mahan'],
            ['fullName' => 'Maître Amadou CAMARA', 'function' => 'Membre du Conseil de l’Ordre', 'portraitReference' => 'amadou_camara'],
        ];
    }

    /** @return array<string, string> */
    public static function load(RichTextSanitizerInterface $sanitizer): array
    {
        return [
            'presentation' => $sanitizer->sanitize(<<<'HTML'
            <p>Le Barreau de Côte d’Ivoire réunit les avocats inscrits au Tableau de l’Ordre ainsi que les avocats stagiaires inscrits sur la liste de stage.</p>
            <h2>Qui sommes-nous ?</h2>
            <p>La profession d’avocat est libérale et indépendante. Les avocats conseillent, assistent et représentent les personnes physiques et morales auprès des juridictions et des administrations, dans le respect de leur déontologie.</p>
            <h2>Nos missions</h2>
            <ul><li>Contribuer au maintien de la compétence professionnelle.</li><li>Veiller au respect des règles professionnelles et déontologiques.</li><li>Faciliter l’accès au droit et à la justice.</li></ul>
            <h2>Nos principes</h2>
            <p>L’exercice de la profession s’inscrit dans des principes d’honneur, d’indépendance, de probité, de délicatesse, de loyauté et de dignité.</p>
            <h2>Organisation institutionnelle</h2>
            <p>Le Barreau est dirigé par le Bâtonnier et administré par le Conseil de l’Ordre. Retrouvez leurs présentations dans les rubriques institutionnelles dédiées.</p>
            HTML),
            'history' => $sanitizer->sanitize(<<<'HTML'
            <p>Cette page rassemble des repères historiques repris de l’ancien site du Barreau. Les dates et périodes ci-dessous sont conservées telles qu’elles y apparaissaient ; elles ne constituent pas une chronologie juridique exhaustive.</p>
            <h2>Avant la mise en place du Barreau</h2>
            <p>L’ancien site évoque les avocats défenseurs qui exerçaient avant la mise en place du Barreau. Il indique qu’ils exerçaient individuellement devant leur juridiction de résidence et qu’ils pouvaient intervenir devant les juridictions de l’A.O.F.</p>
            <h2>1959 — Mise en place du Barreau</h2>
            <p>La source situe la mise en place du Barreau par la loi du 7 novembre 1959.</p>
            <h2>1981 — Évolution du cadre légal</h2>
            <p>La source indique que la loi n°81-588 du 27 juillet 1981 a abrogé et remplacé la loi du 7 novembre 1959.</p>
            <h2>2006 — Libre circulation et établissement dans l’espace UEMOA</h2>
            <p>L’ancien site mentionne le règlement n°10/2006/CM/UEMOA du 25 juillet 2006. Il le présente comme portant sur la libre circulation et l’établissement des avocats ressortissants de l’UEMOA, notamment l’exercice ponctuel dans un Barreau d’accueil et l’établissement à titre principal ou secondaire. Il rappelle également des obligations relatives au domicile élu et au respect des règles du Barreau d’accueil.</p>
            <h2>2014 — Harmonisation des règles professionnelles</h2>
            <p>L’ancien site mentionne le règlement n°05/CM/UEMOA du 25 septembre 2014 et l’associe à l’harmonisation des règles de la profession dans l’espace UEMOA : organisation des Barreaux nationaux, droit de plaidoirie, conditions d’accès et modalités d’exercice.</p>
            <p>Ces références sont reproduites comme des repères historiques cités par l’ancienne page. Elles ne constituent pas un résumé des règles actuellement en vigueur.</p>
            HTML),
            'privacy' => $sanitizer->sanitize(<<<'HTML'
            <h2>1. Données traitées &amp; Finalités</h2>
            <p>Vous pouvez exercer vos droits en nous contactant à l’adresse <a href="mailto:info@ordredesavocats.ci">info@ordredesavocats.ci</a>.</p>
            <h3>1.1. Données de connexion</h3>
            <p>Nous conservons et traitons les informations du journal de serveur Web ainsi que les données générées par l’utilisation de la Plateforme. Les serveurs consultés collectent automatiquement, entre autres, les données suivantes :</p>
            <ul>
                <li>l’adresse IP qui vous est attribuée lors de votre connexion ;</li>
                <li>la date et l’heure d’accès au site ;</li>
                <li>les pages consultées ;</li>
                <li>le type de navigateur (browser) utilisé ;</li>
                <li>la plate-forme et/ou le système d’exploitation installé sur le PC ;</li>
                <li>le moteur de recherche ainsi que les mots-clés utilisés pour retrouver le site.</li>
            </ul>
            <p>Ces informations sont conservées et traitées afin de gérer et sécuriser le site, de mesurer le nombre de visiteurs dans les différentes sections et d’y apporter des améliorations. Elles peuvent également servir à la personnalisation des services et des expériences proposées.</p>
            <p>En cas de violation ou de tentative de violation du système ou en cas d’activité illicite, nous pouvons également utiliser ces informations en coopération avec votre fournisseur d’accès Internet et/ou les autorités locales pour établir et documenter les infractions et en retrouver la source.</p>
            <h3>1.2. Données de contact</h3>
            <p>En vue d’obtenir des services complémentaires (abonnement à une lettre d’information, demande d’informations, prise de RDV, propositions et candidature à des offres d’emploi), il est possible que des informations personnelles, essentiellement des coordonnées de contact (nom, prénom, email, adresse et téléphone) vous soient demandées.</p>
            <p>Ces données ne seront utilisées que pour traiter vos demandes, pour gérer et fournir le service concerné et assurer le suivi normal dudit service.</p>
            <p>Elles peuvent également être utilisées pour mieux cerner vos besoins, pour améliorer le site et à des fins de statistiques internes. Le Barreau de Côte d’Ivoire peut également utiliser vos informations personnelles pour vous contacter et/ou vous fournir des informations générales, de même que des informations sur ses services, dans le cadre de ses missions.</p>
            <h3>1.3. Données sur les utilisateurs de l’espace privé de la Plateforme</h3>
            <p>La Plateforme du Barreau de Côte d’Ivoire offre un espace privé permettant aux adhérents du Barreau de Côte d’Ivoire ayant créé un compte d’accéder à des fonctions avancées. Ces Utilisateurs identifiés seront des personnes physiques, les données traitées sont dès lors des données à caractère personnel.</p>
            <p>Les données traitées concernant les Utilisateurs identifiés sont les suivantes : téléphone, email, fax, date d’inscription, numéro de Toge, adresse géographique, adresse postale, ville, structure et statut.</p>
            <p>Toutes ces données ne constituent que des données utiles dans le cadre du service d’information délivré par la Plateforme. Elles ne contiennent jamais de données sensibles ou de données soumises à des régimes de protection particuliers.</p>
            <p>Le traitement des données à caractère personnel reprises dans la description ci-dessous est effectué dans le cadre de la gestion et l’exploitation de la Plateforme conformément à ses finalités et au service rendu par l’espace privé. Dès lors, ces informations sont strictement réservées à la fourniture de l’accès à l’espace privé et à la communication avec l’administrateur de la Plateforme.</p>
            <h2>2. Sources des données</h2>
            <p>Les données traitées dans le cadre des traitements décrits ci-dessous proviennent des données fournies par les Utilisateurs et les utilisateurs identifiés.</p>
            <h2>3. Mise à jour et conservation des données</h2>
            <p>Le Barreau de Côte d’Ivoire prend des mesures raisonnables pour s’assurer que les données qu’il traite et publie soient fiables pour l’utilisation visée, et aussi précises et complètes que nécessaires pour mener à bien les objectifs décrits dans la présente politique.</p>
            <p>Les Utilisateurs et Utilisateurs identifiés sont par ailleurs invités à vérifier les données les concernant et à spontanément envoyer une requête en rectification en cas d’information désuète ou erronée. Vous êtes également cordialement invité à nous contacter pour toute mise à jour des données vous concernant.</p>
            <p>Les données à caractère personnel faisant partie des traitements seront traitées et conservées pendant le même délai, à moins qu’une personne physique concernée (vous) n’émette le souhait de ne plus voir ses données reprises. Dans ce cas, vos données à caractère personnel seront supprimées dès la réception d’une requête dans ce sens. Vos données peuvent cependant être conservées si le Barreau de Côte d’Ivoire y est contraint par une obligation légale ou à des fins statistiques.</p>
            <h2>4. Transferts et partage des données</h2>
            <p>Le Barreau de Côte d’Ivoire peut être amené à communiquer les données des utilisateurs à des mandataires ou des sous-traitants. Ceux-ci ont interdiction d’utiliser ces données pour une finalité autre que la prestation de services pour le Barreau de Côte d’Ivoire ou dans un cadre autre que leur engagement contractuel.</p>
            <p>Nous pouvons, par exemple, faire appel à des sous-traitants afin d’héberger nos bases de données, de traiter des données ou de vous faire envoyer des informations que vous sollicitez.</p>
            <h2>5. Vos droits concernant le traitement de vos données à caractère personnel</h2>
            <p>Toute personne concernée bénéficie toujours d’un droit d’accès et de rectification de ses données personnelles. Ces droits peuvent être mis en œuvre à tout moment, et vous pouvez également toujours demander la suppression de tout ou partie des données personnelles vous concernant.</p>
            <p>Dans certains cas et à certaines conditions, vous avez également le droit de vous opposer à l’utilisation, ou de demander la limitation ou la portabilité de vos données.</p>
            <p>Vous pouvez exercer vos droits en nous contactant en utilisant les données de contact mentionnées au point 1 ci-dessus. En aucun cas vos données ne seront utilisées à des fins de marketing direct.</p>
            <h2>6. Sécurité</h2>
            <p>Le Barreau de Côte d’Ivoire met en œuvre les mesures techniques, physiques, légales, contractuelles et organisationnelles nécessaires afin de remplir ses obligations légales en matière de vie privée et de protection des données.</p>
            <p>Entre autres, le Barreau de Côte d’Ivoire utilise des services d’hébergement qui sont pourvus de précautions sécuritaires assurant une protection standard de vos données. Vos données sont stockées dans des bases de données sécurisées par des pare-feu.</p>
            <p>Si vous avez des raisons de croire que votre usage de la Plateforme n’est plus sécurisé (par exemple, si vous avez l’impression que la sécurité de vos données a été compromise), nous vous invitons à nous contacter immédiatement via nos coordonnées de contact.</p>
            <h2>7. Modification de la présente politique</h2>
            <p>Le Barreau de Côte d’Ivoire se réserve le droit de porter des modifications à sa politique de protection des données à caractère personnel à tout moment, entre autres, pour prendre en compte des changements dans la finalité de la Plateforme et des exigences légales, toujours en conformité avec la Loi.</p>
            <p>Si nécessaire, toute modification vous sera signalée et, si nous en avons connaissance, par le biais de votre messagerie électronique, ainsi que par tout autre média approprié.</p>
            HTML),
            'legalNotice' => $sanitizer->sanitize(<<<'HTML'
            <p><strong>Version en vigueur du 03 Juillet 2023</strong></p>
            <p>L’Ordre des avocats de Côte d’Ivoire déclare être titulaire de l’ensemble des droits de propriété intellectuelle sur les éléments composant le site accessible via l’adresse <a href="https://web.ordredesavocats.ci">https://web.ordredesavocats.ci</a> et/ou avoir obtenu toutes les autorisations nécessaires.</p>
            <h2>Editeur du Site</h2>
            <p>Le site www.ordredesavocats.ci est géré par l’Ordre des avocats de Côte d’Ivoire :</p>
            <p>Barreau de Côte d’Ivoire<br>Cocody, Les Deux Plateaux ENA, Rue J9<br>Tél. : +225 27 22 41 56 05/13<br>Email : <a href="mailto:info@ordredesavocats.ci">info@ordredesavocats.ci</a></p>
            <h2>Responsable éditorial et de la publication</h2>
            <p>Commission Communication de l’Ordre des Avocats de Côte d’Ivoire.</p>
            <h2>Droits de reproduction</h2>
            <p>L’ensemble des éléments graphiques du site est la propriété d’Ordre des avocats de Côte d’Ivoire. Toute reproduction ou adaptation des pages du site qui en reprendrait les éléments graphiques est strictement interdite. Toute utilisation des contenus à des fins commerciales est également interdite.</p>
            <p>Toute citation ou reprise de contenus du site doit avoir obtenu l’autorisation préalable du Bâtonnier. La source www.ordredesavocats.ci et la date de la copie devront être indiquées ainsi que le Copyright de l’Ordre de avocats de Côte d’Ivoire.</p>
            <h2>Liens vers les pages du site</h2>
            <p>Tout site public ou privé est autorisé à établir des liens vers les pages du www.ordredesavocats.ci. Il n’y a pas à demander d’autorisation préalable.</p>
            <p>Cependant, les pages du site www.ordredesavocats.ci ne devront pas être imbriquées à l’intérieur des pages d’un autre site. Elles devront être affichées dans une nouvelle fenêtre.</p>
            <h2>Conception et Réalisation du site</h2>
            <p>HARRELL GROUP<br>Abidjan, Treichville Belleville, Avenue 21, 26 BP 1512 Abidjan 26<br>Tél. : +2250708683091<br>Email : <a href="mailto:contact@group-harrell.com">contact@group-harrell.com</a><br><a href="https://www.group-harrell.com">www.group-harrell.com</a></p>
            <h2>Hébergement</h2>
            <p>CINETCORE-VENAME<br>Immeuble Toronto, CHU d’Angré<br>Abidjan, Côte d’ivoire<br>Tél. : +225 25 22 02 81 69<br>Email : <a href="mailto:support@vename.com">support@vename.com</a><br><a href="https://www.vename.ci">www.vename.ci</a></p>
            HTML),
            'batonnier' => $sanitizer->sanitize(<<<'HTML'
            <p>Le Bâtonnier est élu au scrutin majoritaire par ses pairs pour un mandat de trois ans. Un an avant son terme, l’assemblée générale élective élit le dauphin appelé à lui succéder.</p>
            <h2>Un rôle de représentation et de direction</h2>
            <p>Le Bâtonnier préside le Conseil de l’Ordre, assure la gestion de l’Ordre et le représente dans les actes de la vie civile, auprès des pouvoirs publics, des juridictions, des autorités et des tiers.</p>
            <h2>Déontologie et règlement des différends</h2>
            <p>Il veille au respect de la déontologie et de la discipline des avocats et exerce l’autorité de poursuite en matière disciplinaire. Il prévient, concilie et résout les différends professionnels entre les membres du Barreau et instruit les réclamations formées par des tiers contre un avocat.</p>
            <p>Pour toute demande, le canal de contact institutionnel est la page <a href="/contact">Contact</a>.</p>
            HTML),
            'council' => $sanitizer->sanitize(<<<'HTML'
            <p>Le Conseil de l’Ordre est l’organe délibérant, législatif et disciplinaire du Barreau. Ses membres sont élus par l’assemblée générale élective au scrutin secret uninominal, tous les trois ans. Ses décisions sont prises par voie d’arrêtés.</p>
            <h2>Organisation et responsabilités</h2>
            <p>Sous la direction du Bâtonnier, le Conseil assure la réglementation intérieure du Barreau, l’administration de l’Ordre et la gestion de ses finances.</p>
            <h2>Ses missions</h2>
            <ul><li>Traiter les questions relatives à la tenue du Tableau : inscription, omission, démission, conditions d’exercice et honorariat.</li><li>Élaborer et tenir à jour le Règlement intérieur du Barreau.</li><li>Fixer le budget et les cotisations.</li><li>Veiller à la défense des droits des avocats ainsi qu’au respect et à l’accomplissement de leurs devoirs.</li></ul>
            <p>La composition actuelle du Conseil est présentée séparément ci-dessous.</p>
            HTML),
            'fund' => $sanitizer->sanitize(<<<'HTML'
            <p>Le Fonds de Solidarité contribue à la solidarité et au bien-être des avocats. La Commission Solidarité et Bien-être du Barreau supervise ses activités, ainsi que celles de l’Observatoire des Droits Humains et de Lutte contre la Corruption et du dispositif de bien-être des avocats.</p>
            <h2>La politique CARE</h2>
            <p>La politique CARE (« prendre soin ») place l’humain au cœur des projets et des interactions de l’Ordre avec les avocats. Elle repose sur cinq piliers :</p>
            <ol><li>La santé pour tous.</li><li>La lutte contre la précarité.</li><li>L’engagement en faveur des droits.</li><li>Le bien-être des avocats.</li><li>L’écoute et le dialogue.</li></ol>
            <h3>Ses objectifs</h3>
            <ul><li>Améliorer le bien-être et la santé des avocats, notamment par la prévention des risques psychosociaux et des maladies qui peuvent amoindrir leurs capacités professionnelles.</li><li>Favoriser un environnement professionnel positif et digne, où les avocats se sentent soutenus et écoutés.</li><li>Encourager le dialogue entre l’Ordre et les avocats et mieux identifier les besoins individuels et collectifs.</li><li>Renforcer la solidarité et le soutien mutuel au sein du Barreau.</li></ul>
            <h2>Des actions de solidarité et de bien-être</h2>
            <h3>Prêts sans intérêts</h3>
            <p>Le Fonds prévoit un dispositif de prêts à taux zéro pour accompagner les avocats dans leurs projets et imprévus. Les modalités figurent dans les ressources réservées aux avocats.</p>
            <h3>Solidarité par le don</h3>
            <p>Un soutien par le don peut être envisagé en cas de difficultés insurmontables. Les modalités et le formulaire sont accessibles dans l’espace avocat.</p>
            <h3>Accompagnement en cas de deuil</h3>
            <p>Le dispositif Yako apporte un soutien financier et moral lors du décès d’un parent, d’un conjoint ou d’un enfant. Pour toute demande ou information, utilisez le <a href="/contact">formulaire de contact du Barreau</a>.</p>
            <h3>Information sur l’assurance santé</h3>
            <p>Le Barreau présente une mutuelle santé destinée aux avocats et gérée par le Fonds de Solidarité. Le guide du réseau de soins est mis à disposition dans l’espace avocat.</p>
            HTML),
            'assistance' => $sanitizer->sanitize(<<<'HTML'
            <p>Le Barreau de Côte d’Ivoire a mis en place le Bureau d’Assistance aux Victimes de Violence Domestique.</p>
            <h2>À qui s’adresse le Bureau ?</h2>
            <p>Le Bureau accompagne juridiquement les femmes victimes de violence domestique.</p>
            <h2>Un accompagnement juridique</h2>
            <p>Les publications du Barreau présentent cet accompagnement comme allant de l’écoute à la défense.</p>
            <h2>Disponibilité</h2>
            <p>Le Barreau indique que le Bureau est ouvert toute l’année.</p>
            <h2>Contacter le Bureau</h2>
            <p>Appeler le Bureau :</p>
            <p><a href="tel:+2250500000254">05 00 00 02 54</a></p>
            <p><a href="tel:+2250500000255">05 00 00 02 55</a></p>
            HTML),
            'carpa' => $sanitizer->sanitize(<<<'HTML'
            <p>La Caisse Autonome de Règlement Pécuniaire des Avocats (CARPA) est l’organisme du Barreau associé aux règlements pécuniaires effectués par les avocats.</p>
            <h2>Une mission au service de la profession</h2>
            <p>Dans le cadre des dossiers confiés aux avocats, les fonds reçus pour le compte de leurs destinataires sont déposés auprès de la CARPA, sous la supervision du Bâtonnier, puis reversés aux bénéficiaires concernés. Les honoraires dus à l’avocat sont également pris en compte dans ce règlement.</p>
            <h2>Un cadre institutionnel</h2>
            <p>La CARPA participe ainsi au cadre de sécurisation des règlements pécuniaires liés à l’activité des avocats. Le règlement intérieur du Barreau comporte des dispositions relatives à ces règlements.</p>
            <p>Pour toute demande d’information, veuillez <a href="/contact">contacter le Barreau</a>.</p>
            HTML),
            'becomeLawyer' => $sanitizer->sanitize(<<<'HTML'
            <h2>Conditions d’accès</h2>
            <ul>
                <li>Être titulaire d’un Master II en droit reconnu par le Conseil Africain Malgache de l’Enseignement Supérieur (CAMES), d’une Maîtrise en droit ou d’un diplôme reconnu équivalent.</li>
                <li>Être titulaire du Certificat d’Aptitude à la Profession d’Avocat (CAPA).</li>
                <li>Être âgé de 21 ans au moins.</li>
                <li>Être de bonne moralité.</li>
            </ul>
            <p>Sont dispensés de justifier du CAPA les magistrats ayant accompli au moins dix années de pratique professionnelle en juridiction et ayant préalablement démissionné de leur fonction, ainsi que les professeurs agrégés des facultés de droit.</p>
            <p>Ces deux catégories doivent, avant la prestation de serment, suivre des cours de déontologie et de pratique professionnelle d’avocat pendant une période de six mois, suivant les modalités définies par le Bâtonnier.</p>
            <h2>Demande d’admission sur la liste de stage</h2>
            <p>La source historique mentionne les pièces suivantes :</p>
            <ul>
                <li>un extrait d’acte de naissance ;</li>
                <li>un extrait de casier judiciaire datant de moins de trois mois ;</li>
                <li>les pièces établissant la nationalité d’un État membre de l’UEMOA ;</li>
                <li>le diplôme de Master II en droit reconnu par le CAMES, la Maîtrise en droit ou un diplôme équivalent ;</li>
                <li>le CAPA ;</li>
                <li>une attestation d’un avocat inscrit au Tableau ayant prêté serment depuis au moins sept ans, s’engageant à assurer dans son cabinet la formation effective du stagiaire.</li>
            </ul>
            <h2>Obligations et responsabilité du stagiaire</h2>
            <ul>
                <li>fréquentation obligatoire des audiences ;</li>
                <li>travail effectif rattaché à un cabinet d’avocat ;</li>
                <li>participation aux travaux de la Conférence du Stage dans les Barreaux qui l’ont instituée ;</li>
                <li>assiduité aux cours de stage.</li>
            </ul>
            <p>La source indique que l’avocat stagiaire accomplit les actes de sa profession pour le compte et sous la responsabilité de l’avocat dans le cabinet duquel il est admis.</p>
            <h2>Durée et fin du stage</h2>
            <p>La source historique indique une durée de stage de trois ans, susceptible d’être prorogée deux fois d’une année. Elle mentionne la délivrance d’un certificat de fin de stage à son terme.</p>
            <h2>Inscription au Tableau de l’Ordre</h2>
            <p>La source historique mentionne les conditions suivantes : avoir effectué trois années de stage, être âgé d’au moins 24 ans, être en possession du certificat de fin de stage et être de bonne moralité.</p>
            HTML),
            'lbc' => $sanitizer->sanitize(<<<'HTML'
            <p>Le Conseil de l’Ordre des Avocats est responsable du contrôle des Avocats titulaires de Cabinet, des Sociétés Civiles Professionnelles d’Avocats et des Associations d’Avocats aux fins de la lutte contre le blanchiment de capitaux et le financement du terrorisme (LBC/FT). Les ressources suivantes sont destinées à aider ces entités déclarantes à comprendre et à respecter leurs obligations en matière de LBC/FT.</p>
            <h2>FORMATION ET CONTROLE THEMATIQUE SUR LES MESURES DE VIGILANCE AUPRES DE LA CLIENTELE</h2>
            <p><a href="https://youtu.be/5YZatNXc5Wg">Voir la vidéo</a></p>
            <h2>SENSIBILISATION À LA MISE EN OEUVRE SANS DÉLAI DES SANCTIONS FINANCIÈRES CIBLÉES</h2>
            <p><a href="https://youtu.be/0E6AMBei1n4">Voir la vidéo</a></p>
            <h2>Autres Ressources Vidéo - Formation sur la LBC/FT</h2>
            <ul>
                <li><a href="https://youtu.be/jn3aEiaYFbo?si=y2kusr-7m98eTQal">🎥 Généralités en matière de LBC/FT</a></li>
                <li><a href="https://youtu.be/NzeMCycTIY8?si=5uko5hqGz3sii1Va">🎥 Obligations des EPNFDs en matière de LBC/FT</a></li>
                <li><a href="https://youtu.be/eTGTta-0krQ?si=ZOyEb_NgSSbUhMem">🎥 Sanctions Financières Ciblées</a></li>
            </ul>
            <h2>Liens utiles</h2>
            <h3>Stratégie nationale LBC/FT et évaluation nationale des risques</h3>
            <ul>
                <li><a href="https://www.centif.ci/cadre-juridique/grandes-lignes-de-la-strategie-nationale-lbc-ft-2020-2030/">Grandes Lignes de la stratégie nationale LBC/FT 2020-2030</a></li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/resume-analytique-evaluation-nationale-des-risques-lbc-ft-padm-cote-d-ivoire-decembre-2019.pdf">Résumé analytique de l’ENR LBC/FT-PADM de la Côte d’Ivoire</a></li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/resumerisquesinherentsft.pdf">Résumé de l’analyse de risque de FT (POPR)</a></li>
            </ul>
            <h3>Résumé analytique de l’ESR LBC/FT-PADM de la Côte d’ivoire</h3>
            <ol>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/RAPPORT-ESR-Agents-Promoteurs-Immobiliers-05-07-2024-VF.docx.pdf">Le secteur des agents et promoteurs immobiliers</a></li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/RAPPORT-ESR-MANDATAIRES-JUDICIAIRES_-26-06-2024.pdf">Le secteur des mandataires judiciaires</a></li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/RAPPORT-ESR-OBNL-vf1.docx.pdf">Les OBNL</a></li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/RAPPORT-ESR-PSSF-REVU-04072024-1-1.docx.pdf">Le secteur des prestataires de service aux sociétés et fiducies</a></li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/RAPPORT-ESR-AGENTS-DAFFAIRES-REVU-05-07-24-VF.docx.pdf">Le secteur des agents d’affaires</a></li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/Evaluation-des-vulnerabilites-de-BC-liees-aux-Personnes-Morales.docx.pdf">Les personnes morales</a></li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/Rapport-Final_Evaluation-Risques-BC-FT_Cote-dIvoire-Resume-Analytique_FINAL-DGDM-mines.docx.pdf">Le secteur des mines</a></li>
            </ol>
            <h3>Législation et réglementation</h3>
            <p>Enquêtes et poursuites criminelles, de la saisie, de la confiscation et de la coopération internationale :</p>
            <ol>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/ORDONNANCE-N2023-875-DU-23-NOVEMBRE-2023-RELATIVE-A-LA-LUTTE-CONTRE-LES-BLANCHIMENTS-DE-CAPIT.pdf">L’Ordonnance n°2023-875 du 23 novembre 2023 relative à la lutte contre les blanchiments de capitaux</a></li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/Decision-N%C2%B021-du-21-decembre-2023-fixant-les-montants-seuils-pour-la-mise-en-oeuvre-de-la-loi-uniforme-relative-a-la-LBCFTFP.pdf">La décision n°021 du 21/12/2023/CM/UMOA</a> fixe les montants des seuils pour la mise en œuvre de la loi uniforme LBC/FT/FP dans les États membres de l’UMOA ;</li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/BCEAO-Decision-n%C2%B0003-du-28_03_2024_CM_UMOA-fixant-les-montants-seuils-complementaires-LBCF_FT_FP-1.pdf">La décision n°003 du 28/03/2024/CM/UMOA</a> (fixe les montants de seuils complémentaires pour le secteur immobilier, les opérations de change manuel, les négociants de pierre et métaux précieux) ;</li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/ORDONNANCE-2022-237-PORTANT-REGIME-DES-SANCTIONS-ADMINISTRATIVES.pdf">L’Ordonnance n°2022-237 du 30 mars 2022 portant régime des sanctions administratives</a> applicables en matière de LBC/FT/FP et organisation du contrôle des assujettis ;</li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/Decret-2024-58-dapplication-de-lordonnance-de-2022-sur-le-controle.pdf">Le décret n°2024-58 du 14 février 2024, portant application de l’ordonnance n°2022-237 du 30 mars 2022</a> portant régime des sanctions administratives applicables en matière de LBC/FT/FP et organisation du contrôle des assujettis, désigne les autorités de supervision de chaque catégorie d’EPNFD et consacre la création de la Commission nationale des sanctions (CNS LBC/FT) qui a vocation à prononcer les sanctions administratives à l’encontre des EPNFD, des SFD et des bureaux de change manuel ;</li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/DECRET-2024-325-du-22-mai-2024-portant-reglementation-de-lactivite-dagent-daffaires-judiciaire.pdf">Le décret n°2024-325 du 22 mai 2024</a> portant règlementation de l’activité d’agent d’affaires judiciaire ;</li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/ARRETE-0415-NOMINATION-DES-MEMBRES-DE-LA-CNS-LBC-FT.pdf">L’arrêté n°0415/MFB/CAB du 02 mai 2024</a> portant nomination des membres de la Commission Nationale de sanction et des membres du secrétariat administratif de la CNS-LBC/FT/FP ;</li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/ARRETE-023-PORTANT-TABLEAU-NATIONAL-DES-MANDATAIRES-JUDICIAIRES-AU-TITRE-DE-LANNEE-2024.pdf">L’arrêté n°023/MJDH/DSJRH du 5 février 2024</a> portant tableau national des mandataires judiciaires au titre de l’année 2024 ;</li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/ARRETE-180-PORTANT-NOMINATION-DES-MEMBRES-DE-LA-COMMISSION-DE-CONTROLE-DES-MANDATAIRES-JUDICIAIRES.pdf">L’arrêté n°180/MJDH/DSJRH du 21 juillet 2023</a> portant nomination des membres de la Commission Nationale de Contrôle des Mandataires Judiciaires (CNCMJ) ;</li>
            </ol>
            <h3>Sanctions Financières Ciblées (SFC)</h3>
            <ol>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/DECRET-N%C2%B02024-216-DU-17-AVRIL-2024-SFC.pdf">Le décret n°2024-216 du 17 avril 2024 relatif à la mise en œuvre des sanctions financières ciblées</a> en matière de FT/FP (établissement d’un mécanisme juridique pour la mise en œuvre des SFC et le rôle central du Ministre chargé des Finances à cet égard) ;</li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/Arrete-0487-CCGA-07-06-2024.pdf">L’arrêté n°0487 du 7 juin 2024 portant attributions, composition et fonctionnement de la commission</a> consultative de gel administratif en abrégé «CCGA» (déléguant certaines des responsabilités du Ministre chargé des Finances à la CCGA et établissant un Secrétariat à cette fin) ;</li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/ARRETE-N-0482-DU-28-JUIN-2024-FINANCES-fixant-les-modalites-de-diffusion-des-listes-SFC-liees-au-FT-FP-1.pdf">L’arrêté interministériel n°0482/MFB/MAEIAIE du 28 Juin 2024 portant modalités de diffusion des listes de sanctions financières ciblées liées au Financement du Terrorisme et de la Prolifération des Armes de Destruction Massives</a> (décrivant le rôle des organes responsables pour certaines tâches relatives aux SFC).</li>
            </ol>
            <h3>Transparence de la propriété des personnes morales et constructions juridique :</h3>
            <ol>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/Loi-n%C2%B02024-362-du-11-juin-2024-portant-creation-du-registre-des-BE-effectifs-des-personnes-morales-et-des-constructions-juridiques.pdf">La loi n°2024-362 du 11 juin 2024 portant création du registre des bénéficiaires effectifs des personnes morales et des constructions juridiques</a> ;</li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/decret-n%C2%B02024-583-du-26-juin-2024-determinant-les-modalites-.pdf">Le décret n°2024-583 du 26 juin 2024 déterminant les modalités d’accès aux informations du registre des bénéficiaires effectifs des personnes morales et des constructions juridiques</a> ;</li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/Circulaire-n%C2%B0-004-MJDH-CAB-du-15-mars-2024.pdf">La circulaire n°004/MJDH/CAB du 15 mars 2024 relative au contrôle interne du registre de commerce et du crédit mobilier (RCCM)</a>.</li>
            </ol>
            <h3>Lignes directrices et ressources pour les assujettis</h3>
            <ul>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/Strategie-actualisee-du-Tresor-Public_VF.pdf">Stratégie_de_contrôle_du_Trésor_Public</a></li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/2024-06-25-Strategie-de-controle-EPNFD-revise-02-07-2024.docx.pdf">Stratégie_de_controle pour les autorités de contrôle des EPNFD</a></li>
            </ul>
            <h3>Déclaration de soupçon et typologies</h3>
            <ul>
                <li><a href="https://www.centif.ci/documents/dos-bon.doc">Modèle Word de la déclaration de soupçon</a></li>
                <li><a href="https://www.centif.ci/typologies/">Typologies LBC/TF</a></li>
            </ul>
            <h3>Études de Typologies en matière de LBC/FT/FP</h3>
            <ol>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/ANALYSE-TYPOLOGIQUE-CYBERCRIMINALITE.pdf">La cybercriminalité</a></li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/ANALYSE-TYPOLOGIQUE-CORRUPTION.pdf">La corruption et les infractions assimilées</a></li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/ANALYSE-TYPOLOGIQUE-FRAUDE-FISCALE.pdf">La fraude fiscale</a></li>
                <li><a href="https://www.centif.ci/wp-content/uploads/2025/03/ANALYSE-TYPOLOGIQUE-CRIMINALITE-ENVIRONEMENTALE.pdf">La criminalité environnementale</a></li>
            </ol>
            <h3>Sanctions financières ciblées TF et PF</h3>
            <ul>
                <li><a href="https://www.centif.ci/liens-utiles/">Ressources CENTIF TFS</a></li>
                <li><a href="https://main.un.org/securitycouncil/fr/content/un-sc-consolidated-list">Liste des Sanctions Consolidées du Conseil de Sécurité des Nations Unies</a></li>
            </ul>
            HTML),
        ];
    }
}

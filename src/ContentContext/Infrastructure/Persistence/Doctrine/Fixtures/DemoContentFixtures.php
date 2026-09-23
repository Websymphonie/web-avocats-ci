<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Fixtures;

use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Domain\Enum\EventFormat;
use Websymphonie\ContentContext\Domain\Enum\EventStatus;
use Websymphonie\ContentContext\Domain\Enum\NewsStatus;
use Websymphonie\ContentContext\Domain\Enum\PageGroup;
use Websymphonie\ContentContext\Domain\Enum\PageStatus;
use Websymphonie\ContentContext\Domain\Enum\EditorialVideoStatus;
use Websymphonie\ContentContext\Domain\Enum\VideoProvider;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\EditorialVideo\EditorialVideoEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\EditorialVideoCategory\EditorialVideoCategoryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Event\EventEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\EventCategory\EventCategoryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Batonnier\BatonnierMandateEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\CouncilMember\CouncilMemberEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\News\NewsEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\NewsCategory\NewsCategoryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Page\PageEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Entity\MediaEntity;

final class DemoContentFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public function __construct(private readonly RichTextSanitizerInterface $sanitizer)
    {
    }

    public static function getGroups(): array
    {
        return ['demo'];
    }

    public function getDependencies(): array
    {
        return [DemoContentTaxonomyFixtures::class, \Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Fixtures\DemoMediaFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        DbLogListener::withoutLogging(function () use ($manager): void {
            $this->loadNews($manager);
            $this->loadEvents($manager);
            $this->loadEditorialVideoCategories($manager);
            $this->loadEditorialVideos($manager);
            $this->loadPages($manager);
            $this->loadInstitutionalPeople($manager);
            $manager->flush();
        });
    }

    private function loadNews(ObjectManager $manager): void
    {
        $titles = [
            'Ouverture des inscriptions à la rencontre professionnelle de rentrée',
            'Le Barreau renforce son programme de formation continue',
            'Modernisation progressive des services numériques du Barreau',
            'Rappel des bonnes pratiques déontologiques en cabinet',
            'Un nouvel espace documentaire pour les avocats',
            'Échange professionnel sur l’accès au droit en Côte d’Ivoire',
            'La commission de formation publie son calendrier trimestriel',
            'Actualisation des ressources consacrées à la procédure civile',
            'Le Barreau poursuit ses actions de sensibilisation juridique',
            'Retour sur la journée consacrée à la pratique du cabinet',
            'Une veille dédiée aux transformations du droit numérique',
            'Nouveaux repères pour la relation entre avocat et client',
            'La bibliothèque juridique enrichit son fonds de référence',
            'Publication d’une note de synthèse sur la justice commerciale',
            'La profession échange sur la confidentialité des échanges',
            'Point d’information sur les prochaines activités institutionnelles',
            'Préparation du prochain atelier de rédaction juridique',
            'La commission numérique présente ses priorités de travail',
            'Des ressources pratiques pour accompagner les jeunes avocats',
            'Le Barreau met en avant les initiatives d’accès au droit',
            'Une nouvelle série de contenus sur la responsabilité professionnelle',
            'Retour d’expérience sur la médiation en matière commerciale',
            'La permanence d’information juridique élargit ses horaires',
            'Bilan intermédiaire des actions de communication institutionnelle',
        ];
        $categoryRefs = ['vie_du_barreau', 'institution', 'profession', 'justice', 'jurisprudence', 'communication'];
        $tagRefs = ['deontologie', 'droit_des_affaires', 'procedure', 'numerique', 'vie_du_barreau', 'acces_au_droit'];
        $now = new DateTimeImmutable();

        foreach ($titles as $index => $title) {
            $number = $index + 1;
            $slug = $this->slug($title);
            $news = $manager->getRepository(NewsEntity::class)->findOneBy(['slug' => $slug]);
            if (!$news instanceof NewsEntity) {
                $news = new NewsEntity();
            }
            $status = $number <= 16 ? NewsStatus::PUBLISHED : ($number <= 22 ? NewsStatus::DRAFT : NewsStatus::ARCHIVED);
            $news->setTitle($title)
                ->setSlug($slug)
                ->setExcerpt('Information de démonstration destinée à tester les listes, filtres et aperçus du Backoffice.')
                ->setBody($this->sanitizer->sanitize(sprintf('<h2>%s</h2><p>Contenu de démonstration du Barreau de Côte d’Ivoire, destiné à alimenter les écrans de test.</p><p>Cette actualité présente des repères fictifs pour évaluer la lecture, la recherche et la navigation éditoriale.</p><ul><li>Un contexte professionnel clairement identifié.</li><li>Une information structurée et facilement consultable.</li></ul>', $title)))
                ->setStatus($status)
                ->setPublishedAt($status === NewsStatus::DRAFT ? null : $now->modify(sprintf('-%d days', 4 + $number * 3)))
                ->setCoverMediaId($number % 3 === 0 ? null : $this->mediaId($number % 2 === 0 ? 'demo_media_content_alt' : 'demo_media_content'));
            $news->replaceCategories([$this->newsCategory('news_category_' . $categoryRefs[$index % count($categoryRefs)])]);
            $news->replaceTags([
                $this->tag('content_tag_' . $tagRefs[$index % count($tagRefs)]),
                $this->tag('content_tag_' . $tagRefs[($index + 2) % count($tagRefs)]),
            ]);
            $manager->persist($news);
            $this->addReference('demo_news_' . str_pad((string) $number, 2, '0', STR_PAD_LEFT), $news);
        }
    }

    private function loadEvents(ObjectManager $manager): void
    {
        $titles = [
            'Conférence de rentrée sur la transformation de la profession',
            'Atelier pratique : organiser la veille juridique du cabinet',
            'Rencontre professionnelle autour de la médiation',
            'Assemblée de travail sur les services aux membres',
            'Cérémonie de présentation des nouveaux inscrits',
            'Conférence sur la procédure et les délais de traitement',
            'Atelier de sensibilisation à la sécurité numérique',
            'Rencontre sur l’accès au droit et l’orientation du justiciable',
            'Assemblée d’information sur les projets institutionnels',
            'Conférence de synthèse sur le droit des affaires',
            'Atelier de rédaction des actes professionnels',
            'Rencontre des commissions spécialisées du Barreau',
            'Cérémonie de clôture du programme de formation',
            'Conférence prospective sur la justice commerciale',
            'Atelier reporté sur la pratique de la plaidoirie',
            'Rencontre annulée de la commission numérique',
            'Assemblée archivée des représentants de la profession',
            'Cérémonie archivée de remise des attestations',
        ];
        $offsets = [-60, -30, -7, 3, 14, 30, 60, 120];
        $categoryRefs = ['conferences', 'ateliers', 'rencontres_professionnelles', 'assemblees', 'ceremonies', 'vie_institutionnelle'];
        $tagRefs = ['profession_avocat', 'procedure', 'droit_des_affaires', 'acces_au_droit', 'numerique', 'vie_du_barreau'];
        $now = new DateTimeImmutable();

        foreach ($titles as $index => $title) {
            $number = $index + 1;
            $slug = $this->slug($title);
            $event = $manager->getRepository(EventEntity::class)->findOneBy(['slug' => $slug]);
            if (!$event instanceof EventEntity) {
                $event = new EventEntity();
            }
            $status = $number <= 10 ? EventStatus::PUBLISHED : ($number <= 14 ? EventStatus::DRAFT : ($number <= 16 ? EventStatus::CANCELLED : EventStatus::ARCHIVED));
            $format = EventFormat::cases()[$index % count(EventFormat::cases())];
            $startsAt = $now->modify(sprintf('%+d days', $offsets[$index % count($offsets)]));
            $event->setTitle($title)
                ->setSlug($slug)
                ->setExcerpt('Événement fictif pour vérifier les filtres par date, format, statut et catégorie.')
                ->setDescription($this->sanitizer->sanitize(sprintf('<h2>%s</h2><p>Cette fiche événement de démonstration permet de tester les formats présentiel, en ligne et hybride.</p><p>Les informations sont fictives et ne constituent pas une annonce réelle.</p>', $title)))
                ->setFormat($format)
                ->setStartsAt($startsAt)
                ->setEndsAt($startsAt->modify('+2 hours'))
                ->setVenueName($format === EventFormat::ONLINE ? null : 'Salle institutionnelle de démonstration')
                ->setAddress($format === EventFormat::ONLINE ? null : 'Adresse fictive — Abidjan')
                ->setOnlineUrl($format === EventFormat::IN_PERSON ? null : 'https://example.test/evenements/' . $slug)
                ->setStatus($status)
                ->setPublishedAt(in_array($status, [EventStatus::DRAFT], true) ? null : $now->modify(sprintf('-%d days', 2 + $number)))
                ->setCoverMediaId($number % 2 === 0 ? $this->mediaId($number % 4 === 0 ? 'demo_media_content_alt' : 'demo_media_content') : null);
            $event->replaceCategories([$this->eventCategory('event_category_' . $categoryRefs[$index % count($categoryRefs)])]);
            $event->replaceTags([
                $this->tag('content_tag_' . $tagRefs[$index % count($tagRefs)]),
                $this->tag('content_tag_' . $tagRefs[($index + 1) % count($tagRefs)]),
            ]);
            $manager->persist($event);
            $this->addReference('demo_event_' . str_pad((string) $number, 2, '0', STR_PAD_LEFT), $event);
        }
    }

    private function loadPages(ObjectManager $manager): void
    {
        $presentationContent = $this->sanitizer->sanitize(<<<'HTML'
            <p>Le Barreau de Côte d’Ivoire réunit les avocats inscrits au Tableau de l’Ordre ainsi que les avocats stagiaires inscrits sur la liste de stage.</p>
            <h2>Qui sommes-nous ?</h2>
            <p>La profession d’avocat est libérale et indépendante. Les avocats conseillent, assistent et représentent les personnes physiques et morales auprès des juridictions et des administrations, dans le respect de leur déontologie.</p>
            <h2>Nos missions</h2>
            <ul><li>Contribuer au maintien de la compétence professionnelle.</li><li>Veiller au respect des règles professionnelles et déontologiques.</li><li>Faciliter l’accès au droit et à la justice.</li></ul>
            <h2>Nos principes</h2>
            <p>L’exercice de la profession s’inscrit dans des principes d’honneur, d’indépendance, de probité, de délicatesse, de loyauté et de dignité.</p>
            <h2>Organisation institutionnelle</h2>
            <p>Le Barreau est dirigé par le Bâtonnier et administré par le Conseil de l’Ordre. Retrouvez leurs présentations dans les rubriques institutionnelles dédiées.</p>
            HTML);

        $historyContent = $this->sanitizer->sanitize(<<<'HTML'
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
            <h2>Les anciens Bâtonniers mentionnés</h2>
            <p>Les noms et périodes ci-dessous reprennent les indications de l’ancien site. Les périodes qui se chevauchent et les dates manquantes sont laissées visibles, sans arbitrage ni correction.</p>
            <h3>Anciens Bâtonniers mentionnés sans période</h3>
            <ul>
            <li>Louis Vigouroux</li><li>Lucien Yapobi</li><li>Armand Josse</li><li>Jean Réveillé</li>
            </ul>
            <h3>Noms et périodes telles qu’affichées</h3>
            <ul>
            <li>René Clarac — 1971–1973</li>
            <li>Maurice Carlton — 1973–1975</li>
            <li>Charles Dogue — 1975–1977</li>
            <li>Camille Adam — 1977–1979</li>
            <li>Jean Konan-Banny — 1979–1980</li>
            <li>Maurice Kakou — 1980–1983</li>
            <li>Julien Mondon-Konan — 1983–1985</li>
            <li>Emile Dervin — 1985–1987</li>
            <li>Amadou Fadika — 1987–1989</li>
            <li>Gabriel Assamoi — 1989–1990</li>
            <li>Charles Gaby Kouassi — 1991–1993</li>
            <li>Lucien N’Gouin Claih — 1993–1995</li>
            <li>Emmanuel Tano K. — 1995–1997</li>
            <li>Essy N’Gatta — 1997–1999</li>
            <li>Luc Adje Kacou — 1999–2001</li>
            <li>Louis Metan — 2000–2003</li>
            <li>Emmanuel Assi — 2005–2007</li>
            <li>Mamadou Kone — 2009–2010 ; 2013–2014</li>
            <li>Joachim Bile-Aka — 2011–2013</li>
            <li>Marcel Beugre — 2014–2015</li>
            <li>Thomas Zé N’Dri — 2019–2021</li>
            <li>Claude Mentenon — 2003–2005 ; 2005–2007 ; 2021–2024</li>
            </ul>
            HTML);

        $batonnierContent = $this->sanitizer->sanitize(<<<'HTML'
            <p>Le Bâtonnier est élu au scrutin majoritaire par ses pairs pour un mandat de trois ans. Un an avant son terme, l’assemblée générale élective élit le dauphin appelé à lui succéder.</p>
            <h2>Un rôle de représentation et de direction</h2>
            <p>Le Bâtonnier préside le Conseil de l’Ordre, assure la gestion de l’Ordre et le représente dans les actes de la vie civile, auprès des pouvoirs publics, des juridictions, des autorités et des tiers.</p>
            <h2>Déontologie et règlement des différends</h2>
            <p>Il veille au respect de la déontologie et de la discipline des avocats et exerce l’autorité de poursuite en matière disciplinaire. Il prévient, concilie et résout les différends professionnels entre les membres du Barreau et instruit les réclamations formées par des tiers contre un avocat.</p>
            <p>Pour toute demande, le canal de contact institutionnel est la page <a href="/contact">Contact</a>.</p>
            HTML);

        $councilContent = $this->sanitizer->sanitize(<<<'HTML'
            <p>Le Conseil de l’Ordre est l’organe délibérant, législatif et disciplinaire du Barreau. Ses membres sont élus par l’assemblée générale élective au scrutin secret uninominal, tous les trois ans. Ses décisions sont prises par voie d’arrêtés.</p>
            <h2>Organisation et responsabilités</h2>
            <p>Sous la direction du Bâtonnier, le Conseil assure la réglementation intérieure du Barreau, l’administration de l’Ordre et la gestion de ses finances.</p>
            <h2>Ses missions</h2>
            <ul><li>Traiter les questions relatives à la tenue du Tableau : inscription, omission, démission, conditions d’exercice et honorariat.</li><li>Élaborer et tenir à jour le Règlement intérieur du Barreau.</li><li>Fixer le budget et les cotisations.</li><li>Veiller à la défense des droits des avocats ainsi qu’au respect et à l’accomplissement de leurs devoirs.</li></ul>
            <p>La composition actuelle du Conseil est présentée séparément ci-dessous.</p>
            HTML);

        $fundContent = $this->sanitizer->sanitize(<<<'HTML'
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
            HTML);

        $carpaContent = $this->sanitizer->sanitize(<<<'HTML'
            <p>La Caisse Autonome de Règlement Pécuniaire des Avocats (CARPA) est l’organisme du Barreau associé aux règlements pécuniaires effectués par les avocats.</p>
            <h2>Une mission au service de la profession</h2>
            <p>Dans le cadre des dossiers confiés aux avocats, les fonds reçus pour le compte de leurs destinataires sont déposés auprès de la CARPA, sous la supervision du Bâtonnier, puis reversés aux bénéficiaires concernés. Les honoraires dus à l’avocat sont également pris en compte dans ce règlement.</p>
            <h2>Un cadre institutionnel</h2>
            <p>La CARPA participe ainsi au cadre de sécurisation des règlements pécuniaires liés à l’activité des avocats. Le règlement intérieur du Barreau comporte des dispositions relatives à ces règlements.</p>
            <p>Pour toute demande d’information, veuillez <a href="/contact">contacter le Barreau</a>.</p>
            HTML);

        /** @var list<array{string, string, PageStatus, bool, PageGroup, int}> $pages */
        $pages = [
            ['Conditions générales d’utilisation', 'conditions-generales-utilisation', PageStatus::PUBLISHED, true, PageGroup::LEGAL, 30],
            ['Politique de confidentialité', 'politique-confidentialite', PageStatus::PUBLISHED, false, PageGroup::LEGAL, 20],
            ['Politique de suppression de compte', 'politique-suppression-compte', PageStatus::PUBLISHED, true, PageGroup::ACCOUNT, 10],
            ['Mentions légales', 'mentions-legales', PageStatus::PUBLISHED, false, PageGroup::LEGAL, 10],
            ['Politique de cookies', 'politique-cookies', PageStatus::DRAFT, true, PageGroup::LEGAL, 40],
            ['Présentation du Barreau', 'presentation', PageStatus::PUBLISHED, false, PageGroup::BAR, 10],
            ['Historique du Barreau', 'historique', PageStatus::PUBLISHED, true, PageGroup::BAR, 20],
            ['Le Bâtonnier', 'le-batonnier', PageStatus::PUBLISHED, false, PageGroup::BAR, 30],
            ['Conseil de l’Ordre', 'conseil-de-l-ordre', PageStatus::PUBLISHED, false, PageGroup::BAR, 40],
            ['Fonds de Solidarité', 'fonds-de-solidarite', PageStatus::PUBLISHED, false, PageGroup::BAR, 50],
            ['Présentation', 'presentation', PageStatus::PUBLISHED, false, PageGroup::CARPA, 10],
        ];
        $now = new DateTimeImmutable();

        foreach ($pages as $index => [$title, $slug, $status, $hasCover, $group, $sortOrder]) {
            $page = $manager->getRepository(PageEntity::class)->findOneBy(['slug' => $slug, 'editorialGroup' => $group]);
            if (!$page instanceof PageEntity) {
                $page = new PageEntity();
            }
            $notice = 'Contenu de démonstration — à valider et adapter juridiquement avant mise en production.';
            $content = $group === PageGroup::CARPA ? $carpaContent : match ($slug) {
                'presentation' => $presentationContent,
                'historique' => $historyContent,
                'le-batonnier' => $batonnierContent,
                'conseil-de-l-ordre' => $councilContent,
                'fonds-de-solidarite' => $fundContent,
                default => $this->sanitizer->sanitize(sprintf('<h2>%s</h2><p>%s</p><p>Cette page fictive sert à préparer les démonstrations et les tests d’interface.</p><ul><li>Présentation structurée du contenu.</li><li>Informations à compléter par l’équipe habilitée.</li></ul>', $title, $notice)),
            };
            $coverMediaId = $slug === 'historique'
                ? $this->mediaId('demo_media_content_bar_history')
                : ($hasCover ? $this->mediaId($index % 2 === 0 ? 'demo_media_content' : 'demo_media_content_alt') : null);
            $page->setTitle($title)
                ->setSlug($slug)
                ->setContent($content)
                ->setStatus($status)
                ->setPublishedAt($status === PageStatus::PUBLISHED ? $now->modify(sprintf('-%d days', 15 + $index)) : null)
                ->setCoverMediaId($coverMediaId)
                ->setGroup($group)
                ->setSortOrder($sortOrder);
            $manager->persist($page);
            $this->addReference('demo_page_' . $group->value . '_' . $slug, $page);
        }
    }

    private function loadInstitutionalPeople(ObjectManager $manager): void
    {
        $florencePortraitId = $this->mediaId('demo_media_institution_portrait_florence_loan_messan');
        $batonnierName = 'Me Florence LOAN épse MESSAN';
        $mandate = $manager->getRepository(BatonnierMandateEntity::class)->findOneBy(['fullName' => $batonnierName]);
        if (!$mandate instanceof BatonnierMandateEntity) {
            $mandate = new BatonnierMandateEntity();
        }
        $mandate->setFullName($batonnierName)
            ->setPortraitMediaId($florencePortraitId)
            ->setMandateStartedAt(new DateTimeImmutable('2024-10-02'))
            ->setMandateEndedAt(null)
            ->setSummary('Première femme à diriger l’Ordre des Avocats de Côte d’Ivoire. Mandat annoncé pour 2024–2027.');
        $manager->persist($mandate);

        /** @var list<array{string, string, string}> $members */
        $members = [
            ['Maître Florence LOAN épse MESSAN', 'Bâtonnier en exercice', 'florence_loan_messan'],
            ['Maître Arouna OUATTARA', 'Secrétaire de l’Ordre', 'arouna_ouattara'],
            ['Bâtonnier Abbé YAO', 'Ancien Bâtonnier 2015-2018', 'abbe_yao'],
            ['Maître Guizot Bernard TAKORE', 'Membre du Conseil de l’Ordre', 'guizot_bernard_takore'],
            ['Maître A. Geneviève SISSOKO épse DIALLO', 'Membre du Conseil de l’Ordre', 'genevieve_sissoko_diallo'],
            ['Maître Marie Françoise N’CHO-KATCHIRE', 'Membre du Conseil de l’Ordre', 'marie_francoise_ncho_katchire'],
            ['Maître Souhalio Lassoman DIOMANDE', 'Membre du Conseil de l’Ordre', 'souhalio_lassoman_diomande'],
            ['Maître Allard Marie-Ernest SENI', 'Membre du Conseil de l’Ordre', 'allard_marie_ernest_seni'],
            ['Maître Bintou Edith ZAN épse LAGO', 'Membre du Conseil de l’Ordre', 'bintou_edith_zan_lago'],
            ['Maître Josiane Josette KOFFI épse BREDOU', 'Membre du Conseil de l’Ordre', 'josiane_josette_koffi_bredou'],
            ['Maître Aïssata DIABI', 'Membre du Conseil de l’Ordre', 'aissata_diabi'],
            ['Maître Nicolas Tompieu MESSAN', 'Membre du Conseil de l’Ordre', 'nicolas_tompieu_messan'],
            ['Maître N’Dry Claver KOUADIO', 'Membre du Conseil de l’Ordre', 'ndry_claver_kouadio'],
            ['Maître Binta BAKAYOKO-MELSEAUX', 'Membre du Conseil de l’Ordre', 'binta_bakayoko_melseaux'],
            ['Maître Yao Philippe GNIMAVO', 'Membre du Conseil de l’Ordre', 'yao_philippe_gnimavo'],
            ['Maître Roseline KOUAME épse KODJO-AKA', 'Membre du Conseil de l’Ordre', 'roseline_kouame_kodjo_aka'],
            ['Maître Maryse BOHOUSSOU', 'Membre du Conseil de l’Ordre', 'maryse_bohoussou'],
            ['Maître Brice Tézaï MAHAN', 'Membre du Conseil de l’Ordre', 'brice_tezai_mahan'],
            ['Maître Amadou CAMARA', 'Membre du Conseil de l’Ordre', 'amadou_camara'],
        ];

        foreach ($members as $index => [$fullName, $function, $portraitReference]) {
            $member = $manager->getRepository(CouncilMemberEntity::class)->findOneBy(['fullName' => $fullName]);
            if (!$member instanceof CouncilMemberEntity) {
                $member = new CouncilMemberEntity();
            }
            $portraitMediaId = $portraitReference === 'florence_loan_messan'
                ? $florencePortraitId
                : $this->mediaId('demo_media_institution_portrait_' . $portraitReference);
            $member->setFullName($fullName)
                ->setFunction($function)
                ->setPortraitMediaId($portraitMediaId)
                ->setSortOrder(($index + 1) * 10)
                ->setMandateStartedAt(null)
                ->setMandateEndedAt(null);
            $manager->persist($member);
        }
    }

    private function loadEditorialVideos(ObjectManager $manager): void
    {
        $videos = [
            ['Regards croisés sur la pratique de la profession', 'regards-croises-sur-la-pratique-de-la-profession', EditorialVideoStatus::PUBLISHED, 1],
            ['Les rendez-vous du Barreau en vidéo', 'les-rendez-vous-du-barreau-en-video', EditorialVideoStatus::PUBLISHED, 4],
            ['Vidéo éditoriale en préparation', 'video-editoriale-en-preparation', EditorialVideoStatus::DRAFT, null],
        ];
        $now = new DateTimeImmutable();

        $categorySlugs = ['vie-du-barreau', 'pratique-professionnelle', 'vie-du-barreau'];
        foreach ($videos as $index => [$title, $slug, $status, $daysAgo]) {
            $video = $manager->getRepository(EditorialVideoEntity::class)->findOneBy(['slug' => $slug]);
            if (!$video instanceof EditorialVideoEntity) {
                $video = new EditorialVideoEntity();
            }
            $video->setTitle($title)
                ->setSlug($slug)
                ->setExcerpt('Un éclairage vidéo de démonstration sur la vie et les pratiques de la profession.')
                ->setDescription($this->sanitizer->sanitize(sprintf('<h2>%s</h2><p>Cette publication vidéo de démonstration présente un contenu éditorial public du Barreau de Côte d’Ivoire.</p>', $title)))
                ->setProvider(VideoProvider::YOUTUBE)
                ->setVideoUrl('https://www.youtube.com/watch?v=M7lc1UVf-VE')
                ->setExternalVideoId('M7lc1UVf-VE')
                ->setCategory($this->editorialVideoCategory($categorySlugs[$index]))
                ->setStatus($status)
                ->setPublishedAt($daysAgo === null ? null : $now->modify(sprintf('-%d days', $daysAgo)));
            $manager->persist($video);
            $this->addReference('demo_editorial_video_' . $slug, $video);
        }
    }

    private function loadEditorialVideoCategories(ObjectManager $manager): void
    {
        foreach ([
            ['Vie du Barreau', 'vie-du-barreau'],
            ['Pratique professionnelle', 'pratique-professionnelle'],
        ] as [$name, $slug]) {
            $category = $manager->getRepository(EditorialVideoCategoryEntity::class)->findOneBy(['slug' => $slug]);
            if (!$category instanceof EditorialVideoCategoryEntity) { $category = new EditorialVideoCategoryEntity(); }
            $category->setName($name)->setSlug($slug)->setDescription('Catégorie de démonstration pour les vidéos éditoriales.');
            $manager->persist($category);
            $this->addReference('demo_editorial_video_category_' . $slug, $category);
        }
    }

    private function mediaId(string $reference): int
    {
        return $this->getReference($reference, MediaEntity::class)->getId() ?? throw new \LogicException(sprintf('Média de fixture sans identifiant : %s', $reference));
    }

    private function newsCategory(string $name): NewsCategoryEntity
    {
        return $this->getReference($name, NewsCategoryEntity::class);
    }

    private function eventCategory(string $name): EventCategoryEntity
    {
        return $this->getReference($name, EventCategoryEntity::class);
    }

    private function editorialVideoCategory(string $slug): EditorialVideoCategoryEntity
    {
        return $this->getReference('demo_editorial_video_category_' . $slug, EditorialVideoCategoryEntity::class);
    }

    private function tag(string $name): TagEntity
    {
        return $this->getReference($name, TagEntity::class);
    }

    private function slug(string $value): string
    {
        return strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value), '-'));
    }
}

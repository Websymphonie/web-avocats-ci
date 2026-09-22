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
        $pages = [
            ['Conditions générales d’utilisation', 'conditions-generales-utilisation', PageStatus::PUBLISHED, true, PageGroup::LEGAL, 30],
            ['Politique de confidentialité', 'politique-confidentialite', PageStatus::PUBLISHED, false, PageGroup::LEGAL, 20],
            ['Politique de suppression de compte', 'politique-suppression-compte', PageStatus::PUBLISHED, true, PageGroup::ACCOUNT, 10],
            ['Mentions légales', 'mentions-legales', PageStatus::PUBLISHED, false, PageGroup::LEGAL, 10],
            ['Politique de cookies', 'politique-cookies', PageStatus::DRAFT, true, PageGroup::LEGAL, 40],
            ['À propos du Barreau', 'a-propos', PageStatus::DRAFT, false, PageGroup::BAR, 10],
        ];
        $now = new DateTimeImmutable();

        foreach ($pages as $index => [$title, $slug, $status, $hasCover, $group, $sortOrder]) {
            $page = $manager->getRepository(PageEntity::class)->findOneBy(['slug' => $slug]);
            if (!$page instanceof PageEntity) {
                $page = new PageEntity();
            }
            $notice = 'Contenu de démonstration — à valider et adapter juridiquement avant mise en production.';
            $page->setTitle($title)
                ->setSlug($slug)
                ->setContent($this->sanitizer->sanitize(sprintf('<h2>%s</h2><p>%s</p><p>Cette page fictive sert à préparer les démonstrations et les tests d’interface.</p><ul><li>Présentation structurée du contenu.</li><li>Informations à compléter par l’équipe habilitée.</li></ul>', $title, $notice)))
                ->setStatus($status)
                ->setPublishedAt($status === PageStatus::PUBLISHED ? $now->modify(sprintf('-%d days', 15 + $index)) : null)
                ->setCoverMediaId($hasCover ? $this->mediaId($index % 2 === 0 ? 'demo_media_content' : 'demo_media_content_alt') : null)
                ->setGroup($group)
                ->setSortOrder($sortOrder);
            $manager->persist($page);
            $this->addReference('demo_page_' . $slug, $page);
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

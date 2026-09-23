<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Functional;

use DateTimeImmutable;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Currencies\Currencies;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;
use Websymphonie\ContentContext\Domain\Enum\EventFormat;
use Websymphonie\ContentContext\Domain\Enum\EventStatus;
use Websymphonie\ContentContext\Domain\Enum\NewsStatus;
use Websymphonie\ContentContext\Domain\Enum\PageGroup;
use Websymphonie\ContentContext\Domain\Enum\PageStatus;
use Websymphonie\ContentContext\Domain\Enum\DocumentAccessLevel;
use Websymphonie\ContentContext\Domain\Enum\DocumentStatus;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\DocumentPublication\DocumentPublicationEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Event\EventEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\News\NewsEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Page\PageEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\CouncilMember\CouncilMemberEntity;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Training\TrainingEntity;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class PublicSearchTest extends WebTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public static function setUpBeforeClass(): void
    {
        foreach ([
            'DATABASE_URL' => 'sqlite:///:memory:',
            'MYSQL_VERSION' => '8.0.40',
            'SECURE_SCHEME' => 'https',
        ] as $name => $value) {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }

        parent::setUpBeforeClass();
    }

    public function testPublicSearchReturnsPublishedNewsEventsAndPublicTrainings(): void
    {
        $client = $this->clientWithSchema();
        $this->createDataset();

        $client->request('GET', '/recherche/autocomplete?q=Droit', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('Droit', $payload['query']);
        self::assertCount(3, $payload['results']);
        self::assertSame(['news', 'event', 'training'], array_column($payload['results'], 'type'));
        self::assertSame('/actualites/droit-publie', $payload['results'][0]['url']);
        self::assertSame('/evenements/droit-evenement', $payload['results'][1]['url']);
        self::assertSame('/formations/droit-formation', $payload['results'][2]['url']);
        self::assertArrayNotHasKey('description', $payload['results'][0]);
        self::assertArrayNotHasKey('joinUrl', $payload['results'][2]);
    }

    public function testPublicSearchDoesNotExposeDraftOrMemberContentAndHandlesShortTerms(): void
    {
        $client = $this->clientWithSchema();
        $this->createDataset();

        $client->request('GET', '/recherche/autocomplete?q=D', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSame([], json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR)['results']);

        $client->request('GET', '/recherche/autocomplete?q=secret', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringNotContainsString('Brouillon secret', $content);
        self::assertStringNotContainsString('Formation membre secrète', $content);

        $client->request('GET', '/recherche/autocomplete?q=nonpubliee', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSame([], json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR)['results']);
    }

    public function testPublicSearchReturnsPublishedInformationWithGroupMetadataAndCanonicalUrl(): void
    {
        $client = $this->clientWithSchema();
        $this->createDataset();

        $client->request('GET', '/recherche/autocomplete?q=Mentions', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(1, $payload['results']);
        self::assertSame([
            'type' => 'information',
            'title' => 'Mentions légales',
            'url' => '/informations/mentions-legales',
            'metadata' => 'Informations légales',
        ], $payload['results'][0]);

        $client->request('GET', '/recherche/autocomplete?q=Vie privée', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(1, $payload['results']);
        self::assertSame('information', $payload['results'][0]['type']);
        self::assertSame('Informations légales', $payload['results'][0]['metadata']);
        self::assertSame('/informations/politique-confidentialite', $payload['results'][0]['url']);
        self::assertSame('Vie privée', $payload['results'][0]['title']);

        $client->request('GET', '/recherche/autocomplete?q=Présentation', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(1, $payload['results']);
        self::assertSame('Le Barreau', $payload['results'][0]['metadata']);
        self::assertSame('/le-barreau/presentation', $payload['results'][0]['url']);

        $client->request('GET', '/recherche/autocomplete?q=Historique', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(1, $payload['results']);
        self::assertSame([
            'type' => 'information',
            'title' => 'Historique du Barreau',
            'url' => '/le-barreau/historique',
            'metadata' => 'Le Barreau',
        ], $payload['results'][0]);

        $client->request('GET', '/recherche/autocomplete?q=Guide', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(1, $payload['results']);
        self::assertSame('information', $payload['results'][0]['type']);
        self::assertSame('Informations', $payload['results'][0]['metadata']);
        self::assertSame('/informations/guide-visiteur', $payload['results'][0]['url']);

        $client->request('GET', '/recherche/autocomplete?q=politique-confidentialite', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(1, $payload['results']);
        self::assertSame('Vie privée', $payload['results'][0]['title']);
    }

    public function testLbcPageSearchUsesItsDedicatedCanonicalUrl(): void
    {
        $client = $this->clientWithSchema();
        $this->createDataset();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist((new PageEntity())
            ->setTitle('Lutte contre le Blanchiment des Capitaux (LBC/FT/FP)')
            ->setSlug('lbc-ft-fp')
            ->setContent('<p>Ressources.</p>')
            ->setGroup(PageGroup::LBC)
            ->setStatus(PageStatus::PUBLISHED)
            ->setPublishedAt(new DateTimeImmutable('-1 day')));
        $entityManager->flush();

        $client->request('GET', '/recherche/autocomplete?q=Blanchiment', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(1, $payload['results']);
        self::assertSame('/lbc-ft-fp', $payload['results'][0]['url']);
        self::assertSame('LBC/FT/FP', $payload['results'][0]['metadata']);
    }

    public function testDomesticViolenceAssistancePageIsSearchableByItsSubjectAndUsesCanonicalUrl(): void
    {
        $client = $this->clientWithSchema();
        $this->createDataset();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist((new PageEntity())
            ->setTitle('Bureau d’Assistance aux Victimes de Violence Domestique')
            ->setSlug('assistance-violences-domestiques')
            ->setContent('<p>Le Bureau accompagne juridiquement les femmes victimes de violence domestique.</p>')
            ->setGroup(null)
            ->setStatus(PageStatus::PUBLISHED)
            ->setPublishedAt(new DateTimeImmutable('-1 day')));
        $entityManager->flush();

        foreach (['violence', 'violence domestique', 'assistance', 'victime'] as $term) {
            $client->request('GET', '/recherche/autocomplete', ['q' => $term], server: ['HTTPS' => 'on']);

            self::assertResponseIsSuccessful();
            $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $matches = array_values(array_filter($payload['results'], static fn (array $result): bool => $result['title'] === 'Bureau d’Assistance aux Victimes de Violence Domestique'));
            self::assertCount(1, $matches, 'Expected a search result for: ' . $term);
            self::assertSame('/assistance-violences-domestiques', $matches[0]['url']);
        }
    }

    public function testCarpaPageIsExcludedWhileDraft(): void
    {
        $client = $this->clientWithSchema();
        $this->createDataset();

        $client->request('GET', '/recherche/autocomplete?q=CARPA', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSame([], json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR)['results']);
    }

    public function testPublishedCarpaPageIsSearchableAtItsCanonicalUrl(): void
    {
        $client = $this->clientWithSchema();
        $this->createDataset();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $carpaPage = $entityManager->getRepository(PageEntity::class)->findOneBy(['slug' => 'presentation', 'editorialGroup' => PageGroup::CARPA]);
        self::assertInstanceOf(PageEntity::class, $carpaPage);
        $carpaPage->setContent('<p>Présentation institutionnelle de la CARPA.</p>')
            ->setStatus(PageStatus::PUBLISHED)
            ->setPublishedAt(new DateTimeImmutable('-1 day'));
        $entityManager->flush();

        $client->request('GET', '/recherche/autocomplete?q=CARPA', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(1, $payload['results']);
        self::assertSame([
            'type' => 'information',
            'title' => 'Présentation',
            'url' => '/carpa/presentation',
            'metadata' => 'La CARPA',
        ], $payload['results'][0]);
    }

    public function testCurrentInstitutionalPagesAreSearchableButCouncilMembersAreNot(): void
    {
        $client = $this->clientWithSchema();
        $this->createDataset();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        foreach ([
            ['Le Bâtonnier', 'le-batonnier'],
            ['Conseil de l’Ordre', 'conseil-de-l-ordre'],
        ] as [$title, $slug]) {
            $entityManager->persist((new PageEntity())
                ->setTitle($title)
                ->setSlug($slug)
                ->setContent('<p>Information institutionnelle.</p>')
                ->setGroup(PageGroup::BAR)
                ->setStatus(PageStatus::PUBLISHED)
                ->setPublishedAt(new DateTimeImmutable('-1 day')));
        }
        $entityManager->persist((new PageEntity())
            ->setTitle('Fonds de Solidarité')
            ->setSlug('fonds-de-solidarite')
            ->setContent('<h2>La politique CARE</h2><p>Une information institutionnelle publique.</p>')
            ->setGroup(PageGroup::BAR)
            ->setSortOrder(50)
            ->setStatus(PageStatus::PUBLISHED)
            ->setPublishedAt(new DateTimeImmutable('-1 day')));
        $entityManager->persist((new CouncilMemberEntity())
            ->setFullName('Maître Arouna OUATTARA')
            ->setFunction('Secrétaire de l’Ordre')
            ->setSortOrder(20));
        $entityManager->persist((new DocumentPublicationEntity())
            ->setTitle('Formulaire de demande de prêt')
            ->setSlug('fonds-solidarite-demande-pret')
            ->setDescription('Formulaire réservé aux avocats.')
            ->setStoredFileId(1)
            ->setAccessLevel(DocumentAccessLevel::LAWYER)
            ->setStatus(DocumentStatus::PUBLISHED)
            ->setPublishedAt(new DateTimeImmutable('-1 day')));
        $entityManager->persist((new DocumentPublicationEntity())
            ->setTitle('Règlement intérieur du Barreau de Côte d’Ivoire')
            ->setSlug('reglement-interieur-barreau-cote-ivoire')
            ->setDescription('Texte institutionnel public comportant une section relative aux règlements pécuniaires.')
            ->setStoredFileId(1)
            ->setAccessLevel(DocumentAccessLevel::PUBLIC)
            ->setStatus(DocumentStatus::PUBLISHED)
            ->setPublishedAt(new DateTimeImmutable('-1 day')));
        $entityManager->flush();

        $client->request('GET', '/recherche/autocomplete?q=Bâtonnier', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame([
            'type' => 'information',
            'title' => 'Le Bâtonnier',
            'url' => '/le-barreau/le-batonnier',
            'metadata' => 'Le Barreau',
        ], $payload['results'][0]);

        $client->request('GET', '/recherche/autocomplete?q=Conseil de l’Ordre', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(1, $payload['results']);
        self::assertSame('/le-barreau/conseil-de-l-ordre', $payload['results'][0]['url']);

        $client->request('GET', '/recherche/autocomplete?q=Fonds de Solidarité', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame([
            'type' => 'information',
            'title' => 'Fonds de Solidarité',
            'url' => '/le-barreau/fonds-de-solidarite',
            'metadata' => 'Le Barreau',
        ], $payload['results'][0]);

        $client->request('GET', '/recherche/autocomplete?q=Formulaire de demande de prêt', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame([], $payload['results']);

        $client->request('GET', '/recherche/autocomplete?q=Règlement intérieur du Barreau', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame([], $payload['results']);

        $client->request('GET', '/recherche/autocomplete?q=Arouna OUATTARA', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame([], $payload['results']);
    }

    public function testSearchEntryPointAndDialogAreAvailableOnThePublicLayout(): void
    {
        $client = $this->clientWithSchema();

        $client->request('GET', '/', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('button[aria-label="Rechercher"][aria-controls="public-search-dialog"]');
        self::assertSelectorExists('button[data-controller="theme-toggle"]');

        $client->request('GET', '/formations', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('button[aria-label="Rechercher"][aria-controls="public-search-dialog"]');
        self::assertSelectorExists('dialog#public-search-dialog[aria-modal="true"]');
        self::assertSelectorTextContains('dialog#public-search-dialog', 'Actualités');
        self::assertSelectorTextContains('dialog#public-search-dialog', 'Événements');
        self::assertSelectorTextContains('dialog#public-search-dialog', 'Formations');
    }

    private function clientWithSchema(): \Symfony\Bundle\FrameworkBundle\KernelBrowser
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        (new SchemaTool($entityManager))->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        $entityManager->persist(new Reglages('app_title', 'Application title', 'Avocat CI', 'text'));
        $entityManager->persist(new Reglages('app_paginate_limit', 'Pagination', '10', 'number'));
        $entityManager->persist((new Images())->setName('app_favicon')->setLabel('Favicon'));
        $entityManager->persist((new Currencies())->setCurrencyCode('XOF')->setCurrencyName('Franc CFA')->setRightSymbol('FCFA')->setDecimalPlace(0)->setIsActive(true));
        $entityManager->flush();
        $client->disableReboot();

        return $client;
    }

    private function createDataset(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $publishedAt = new DateTimeImmutable('-1 day');

        $news = (new NewsEntity())
            ->setTitle('Droit publié')
            ->setSlug('droit-publie')
            ->setExcerpt('Actualité sur le droit ivoirien.')
            ->setBody('<p>Contenu public.</p>')
            ->setStatus(NewsStatus::PUBLISHED)
            ->setPublishedAt($publishedAt);
        $draftNews = (new NewsEntity())
            ->setTitle('Brouillon secret')
            ->setSlug('brouillon-secret')
            ->setExcerpt('Ne doit pas apparaître.')
            ->setBody('<p>Brouillon.</p>');

        $event = (new EventEntity())
            ->setTitle('Droit événement')
            ->setSlug('droit-evenement')
            ->setExcerpt('Rendez-vous public sur le droit.')
            ->setDescription('<p>Événement public.</p>')
            ->setFormat(EventFormat::IN_PERSON)
            ->setStartsAt(new DateTimeImmutable('+3 days'))
            ->setVenueName('Maison du Barreau')
            ->setAddress('Abidjan')
            ->setStatus(EventStatus::PUBLISHED)
            ->setPublishedAt($publishedAt);

        $training = (new TrainingEntity(TrainingType::COURSE))
            ->setTitle('Droit formation')
            ->setSlug('droit-formation')
            ->setSummary('Formation publique sur le droit.')
            ->setDescription('<p>Contenu de formation.</p>')
            ->setVisibility(TrainingVisibility::PUBLIC)
            ->setAccessType(TrainingAccessType::FREE)
            ->setStatus(TrainingStatus::PUBLISHED)
            ->setPublishedAt($publishedAt);
        $memberTraining = (new TrainingEntity(TrainingType::COURSE))
            ->setTitle('Formation membre secrète')
            ->setSlug('formation-membre-secrete')
            ->setSummary('Contenu réservé.')
            ->setDescription('<p>Privé.</p>')
            ->setVisibility(TrainingVisibility::MEMBER)
            ->setAccessType(TrainingAccessType::RESTRICTED)
            ->setStatus(TrainingStatus::PUBLISHED)
            ->setPublishedAt($publishedAt);
        $legalPage = (new PageEntity())
            ->setTitle('Mentions légales')
            ->setSlug('mentions-legales')
            ->setContent('<p>Informations légales publiques.</p>')
            ->setGroup(PageGroup::LEGAL)
            ->setStatus(PageStatus::PUBLISHED)
            ->setPublishedAt($publishedAt);
        $accountPage = (new PageEntity())
            ->setTitle('Vie privée')
            ->setSlug('politique-confidentialite')
            ->setContent('<p>Informations de confidentialité publiques.</p>')
            ->setGroup(PageGroup::LEGAL)
            ->setStatus(PageStatus::PUBLISHED)
            ->setPublishedAt($publishedAt);
        $draftPage = (new PageEntity())
            ->setTitle('Page secrète brouillon')
            ->setSlug('page-secrete-brouillon')
            ->setContent('<p>Brouillon.</p>')
            ->setGroup(PageGroup::LEGAL)
            ->setStatus(PageStatus::DRAFT);
        $carpaPage = (new PageEntity())
            ->setTitle('Présentation')
            ->setSlug('presentation')
            ->setContent('')
            ->setGroup(PageGroup::CARPA)
            ->setStatus(PageStatus::DRAFT);
        $unpublishedPage = (new PageEntity())
            ->setTitle('Information non publiée')
            ->setSlug('information-nonpubliee')
            ->setContent('<p>Sans date de publication.</p>')
            ->setStatus(PageStatus::PUBLISHED);
        $ungroupedPage = (new PageEntity())
            ->setTitle('Guide du visiteur')
            ->setSlug('guide-visiteur')
            ->setContent('<p>Information publique sans groupe.</p>')
            ->setStatus(PageStatus::PUBLISHED)
            ->setPublishedAt($publishedAt);
        $barPage = (new PageEntity())
            ->setTitle('Présentation du Barreau')
            ->setSlug('presentation')
            ->setContent('<p>Présentation institutionnelle publique.</p>')
            ->setGroup(PageGroup::BAR)
            ->setStatus(PageStatus::PUBLISHED)
            ->setPublishedAt($publishedAt);
        $barHistoryPage = (new PageEntity())
            ->setTitle('Historique du Barreau')
            ->setSlug('historique')
            ->setContent('<p>Repères historiques du Barreau.</p>')
            ->setGroup(PageGroup::BAR)
            ->setStatus(PageStatus::PUBLISHED)
            ->setPublishedAt($publishedAt);

        foreach ([$news, $draftNews, $event, $training, $memberTraining, $legalPage, $accountPage, $draftPage, $carpaPage, $unpublishedPage, $ungroupedPage, $barPage, $barHistoryPage] as $item) {
            $entityManager->persist($item);
        }
        $entityManager->flush();
    }
}

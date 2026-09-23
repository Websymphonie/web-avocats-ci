<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;
use Websymphonie\ContentContext\Domain\Enum\PageGroup;
use Websymphonie\ContentContext\Domain\Enum\PageStatus;
use Websymphonie\ContentContext\Infrastructure\Bootstrap\InstitutionalContentBootstrapper;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Batonnier\BatonnierMandateEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\CouncilMember\CouncilMemberEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Event\EventEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\News\NewsEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Page\PageEntity;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class ReleaseBootstrapTest extends WebTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public static function setUpBeforeClass(): void
    {
        foreach ([
            'DATABASE_URL' => 'sqlite:///:memory:',
            'SECURE_SCHEME' => 'https',
        ] as $name => $value) {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }

        parent::setUpBeforeClass();
    }

    public function testReleaseBootstrapsAreIdempotentAndServePublicPagesWithoutDemoData(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        (new SchemaTool($entityManager))->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        $systemBootstrap = static::getContainer()->get(\Websymphonie\AdminContext\Infrastructure\Bootstrap\SystemBootstrapper::class);
        $institutionalBootstrap = static::getContainer()->get(InstitutionalContentBootstrapper::class);
        $client->disableReboot();

        $client->request('GET', '/', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK, 'homepage sans réglages préinstallés');
        $client->request('GET', '/auth/login', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK, 'login sans réglages préinstallés');

        self::assertSame(['settings' => 4, 'images' => 2], $systemBootstrap->bootstrap());
        self::assertSame(['settings' => 0, 'images' => 0], $systemBootstrap->bootstrap());
        self::assertSame(['pages' => 11, 'batonnier' => 1, 'councilMembers' => 19], $institutionalBootstrap->bootstrap());

        $becomeLawyer = $entityManager->getRepository(PageEntity::class)->findOneBy([
            'editorialGroup' => PageGroup::PROFESSION,
            'slug' => 'devenir-avocat',
        ]);
        self::assertInstanceOf(PageEntity::class, $becomeLawyer);
        self::assertSame(PageStatus::DRAFT, $becomeLawyer->getStatus());
        $becomeLawyerUuid = $becomeLawyer->getUuidAsString();

        self::assertSame(['pages' => 0, 'batonnier' => 0, 'councilMembers' => 0], $institutionalBootstrap->bootstrap());
        $entityManager->clear();

        self::assertSame(11, $entityManager->getRepository(PageEntity::class)->count([]));
        self::assertSame(1, $entityManager->getRepository(BatonnierMandateEntity::class)->count([]));
        self::assertSame(19, $entityManager->getRepository(CouncilMemberEntity::class)->count([]));
        self::assertSame(0, $entityManager->getRepository(User::class)->count([]));
        self::assertSame(0, $entityManager->getRepository(NewsEntity::class)->count([]));
        self::assertSame(0, $entityManager->getRepository(EventEntity::class)->count([]));

        $becomeLawyerAfterSecondRun = $entityManager->getRepository(PageEntity::class)->findOneBy([
            'editorialGroup' => PageGroup::PROFESSION,
            'slug' => 'devenir-avocat',
        ]);
        self::assertSame($becomeLawyerUuid, $becomeLawyerAfterSecondRun?->getUuidAsString());
        self::assertSame(PageStatus::DRAFT, $becomeLawyerAfterSecondRun?->getStatus());

        foreach ([
            '/',
            '/auth/login',
            '/le-barreau',
            '/le-barreau/presentation',
            '/carpa',
            '/carpa/presentation',
            '/lbc-ft-fp',
            '/informations/mentions-legales',
            '/informations/politique-confidentialite',
            '/assistance-violences-domestiques',
            '/contact',
            '/avocats',
        ] as $path) {
            $client->request('GET', $path, server: ['HTTPS' => 'on']);
            self::assertResponseStatusCodeSame(Response::HTTP_OK, $path);
        }

        $client->request('GET', '/devenir-avocat', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $client->request('GET', '/espace', server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/auth/login');
        $client->request('GET', '/admin', server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/auth/login');

        self::assertInstanceOf(Reglages::class, $entityManager->getRepository(Reglages::class)->findOneBy(['name' => 'app_title']));
        self::assertInstanceOf(Images::class, $entityManager->getRepository(Images::class)->findOneBy(['name' => 'app_logo']));
    }
}

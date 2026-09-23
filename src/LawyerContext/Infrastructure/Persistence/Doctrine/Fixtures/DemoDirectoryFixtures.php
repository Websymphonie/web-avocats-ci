<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\Cabinet\CabinetEntity;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\LawyerProfile\LawyerProfileEntity;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Entity\MediaEntity;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Fixtures\DemoMediaFixtures;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;

final class DemoDirectoryFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public static function getGroups(): array
    {
        return ['demo'];
    }

    public function getDependencies(): array
    {
        return [
            \Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Fixtures\UserFixtures::class,
            DemoMediaFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        DbLogListener::withoutLogging(function () use ($manager): void {
            $publicCabinet = $this->cabinet($manager, 'Cabinet de démonstration — annuaire public', 'DEMO-PUBLIC');
            $publicCabinet->setCity('Abidjan')->setAddress('Adresse de démonstration')->setDirectoryVisible(true);

            $privateCabinet = $this->cabinet($manager, 'Cabinet de démonstration — non publié', 'DEMO-PRIVATE');
            $privateCabinet->setCity('Abidjan')->setDirectoryVisible(false);

            $portrait = $this->getReference('demo_media_institution_lawyer_directory_demo', MediaEntity::class);
            $this->profile($manager, 'directory-public-cabinet@example.test', 'Profil Avocat Démo — cabinet', 'directory-cabinet@example.test', '+225 01 00 00 00 01', 'ACTIVE', true, $publicCabinet, $portrait->getId());
            $this->profile($manager, 'directory-public-independent@example.test', 'Profil Avocat Démo — indépendant', 'directory-independent@example.test', '+225 01 00 00 00 02', 'HONORARY', true, null, null);
            $this->profile($manager, 'directory-private@example.test', 'Profil Avocat Démo — non publié', 'directory-private@example.test', null, 'ACTIVE', false, $privateCabinet, null);
            $this->profile($manager, 'directory-suspended@example.test', 'Profil Avocat Démo — suspendu', 'directory-suspended@example.test', null, 'SUSPENDED', true, null, null);

            $manager->flush();
        });
    }

    private function cabinet(ObjectManager $manager, string $name, string $registrationNumber): CabinetEntity
    {
        $cabinet = $manager->getRepository(CabinetEntity::class)->findOneBy(['registrationNumber' => $registrationNumber]);
        if (!$cabinet instanceof CabinetEntity) {
            $cabinet = (new CabinetEntity())->setName($name)->setRegistrationNumber($registrationNumber);
            $manager->persist($cabinet);
        }

        return $cabinet;
    }

    private function profile(
        ObjectManager $manager,
        string $accountEmail,
        string $displayName,
        string $professionalEmail,
        ?string $professionalPhone,
        string $professionalStatus,
        bool $directoryVisible,
        ?CabinetEntity $cabinet,
        ?int $portraitMediaId,
    ): void {
        $user = $manager->getRepository(User::class)->findOneBy(['email' => $accountEmail]);
        if (!$user instanceof User) {
            $user = new User();
            $user->setEmail($accountEmail)
                ->setPassword($this->passwordHasher->hashPassword($user, bin2hex(random_bytes(32))))
                ->setRoles(['ROLE_AVOCAT'])
                ->setEnabled(true);
        }
        $user->setName($displayName);
        $manager->persist($user);

        $profile = $manager->getRepository(LawyerProfileEntity::class)->findOneBy(['user' => $user]);
        if (!$profile instanceof LawyerProfileEntity) {
            $profile = (new LawyerProfileEntity())->setUser($user);
            $manager->persist($profile);
        }

        $profile->setDisplayName($displayName)
            ->setCabinet($cabinet)
            ->setProfessionalStatus($professionalStatus)
            ->setDirectoryVisible($directoryVisible)
            ->setProfessionalEmail($professionalEmail)
            ->setProfessionalPhone($professionalPhone)
            ->setPortraitMediaId($portraitMediaId)
            ->setSpecializationSummary('Données fictives de démonstration');
    }
}

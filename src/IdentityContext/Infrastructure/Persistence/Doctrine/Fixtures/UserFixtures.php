<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

final class UserFixtures extends Fixture
{
    public const string EMAIL = 'dev@web-symphonie.com';
    public const string PASSWORD = '123456789';

    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user = $manager->getRepository(User::class)->findOneBy(['email' => self::EMAIL]);
        $user = $user instanceof User ? $user : new User();

        $user->setName('Koffi Tchimou Joel');
        $user->setEmail(self::EMAIL);
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $user->setEnabled(true);
        $user->setPassword($this->passwordHasher->hashPassword($user, self::PASSWORD));

        $manager->persist($user);
        $manager->flush();
    }
}

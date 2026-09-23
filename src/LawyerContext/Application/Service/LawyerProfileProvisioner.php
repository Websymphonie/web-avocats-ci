<?php
declare(strict_types=1);

namespace Websymphonie\LawyerContext\Application\Service;

use Doctrine\ORM\EntityManagerInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\LawyerProfile\LawyerProfileEntity;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Repository\LawyerProfile\LawyerProfileRepository;

final readonly class LawyerProfileProvisioner
{
    public function __construct(private LawyerProfileRepository $profiles, private EntityManagerInterface $entityManager) {}

    public function ensureFor(User $user): LawyerProfileEntity
    {
        $profile = $this->profiles->findOneByUser($user);
        if ($profile !== null) {
            return $profile;
        }
        $profile = (new LawyerProfileEntity())->setUser($user);
        $this->entityManager->persist($profile);
        $this->entityManager->flush();
        return $profile;
    }
}

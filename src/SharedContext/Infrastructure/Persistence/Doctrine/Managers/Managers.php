<?php declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Managers;

use Doctrine\ORM\EntityManagerInterface;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

final readonly class Managers implements ManagersInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    )
    {
    }

    public function execute(object $objet, DbActionEnum $action, bool $flush = true): void
    {
        $this->em->wrapInTransaction(function () use ($objet, $action, $flush): void {
            match ($action) {
                DbActionEnum::NEW => $this->em->persist($objet),
                DbActionEnum::EDIT => null, // rien, l’entité est déjà managée
                DbActionEnum::DELETE => $this->em->remove($objet),
            };

            if ($flush) {
                $this->em->flush();
            }
        });
    }
}


<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Application\Usecase\QueryHandler;

use Websymphonie\LawyerContext\Application\Usecase\Query\GetPublicCabinetProfileQuery;
use Websymphonie\LawyerContext\Domain\Model\CabinetPublicProfile;
use Websymphonie\LawyerContext\Domain\Repository\LawyerDirectoryRepositoryInterface;
use Websymphonie\LawyerContext\Domain\Repository\PublicCabinetDirectoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetPublicCabinetProfileQueryHandler implements QueryHandler
{
    public function __construct(
        private PublicCabinetDirectoryRepositoryInterface $cabinets,
        private LawyerDirectoryRepositoryInterface $lawyers,
    ) {
    }

    public function __invoke(GetPublicCabinetProfileQuery $query): ?CabinetPublicProfile
    {
        $cabinet = $this->cabinets->findPublicByUuid($query->uuid);
        if ($cabinet === null) {
            return null;
        }

        return new CabinetPublicProfile(
            publicUuid: $cabinet->publicUuid,
            name: $cabinet->name,
            address: $cabinet->address,
            city: $cabinet->city,
            country: $cabinet->country,
            phone: $cabinet->phone,
            email: $cabinet->email,
            websiteUrl: $cabinet->websiteUrl,
            description: $cabinet->description,
            members: $this->lawyers->listPublicMembersByCabinetUuid($cabinet->publicUuid),
        );
    }
}

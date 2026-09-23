<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Application\Service;

/** V1 publication criteria; this is not proof of official Bar registration. */
final class LawyerDirectoryPublicationPolicy
{
    /** @param list<string> $userRoles */
    public function isLawyerEligible(bool $directoryVisible, bool $userEnabled, array $userRoles, string $professionalStatus): bool
    {
        return $directoryVisible
            && $userEnabled
            && in_array('ROLE_AVOCAT', $userRoles, true)
            && $professionalStatus !== 'SUSPENDED';
    }

    public function isCabinetEligible(string $status, bool $directoryVisible): bool
    {
        return $directoryVisible && $status === 'ACTIVE';
    }
}

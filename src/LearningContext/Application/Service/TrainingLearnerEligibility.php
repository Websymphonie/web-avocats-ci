<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Service;

use Websymphonie\IdentityContext\Application\Service\User\UserDirectoryInterface;

final readonly class TrainingLearnerEligibility implements TrainingLearnerEligibilityInterface
{
    public function __construct(private UserDirectoryInterface $users)
    {
    }

    public function isEligible(int $userId): bool
    {
        $user = $this->users->getById($userId);

        return $user !== null
            && $user->enabled
            && in_array('ROLE_AVOCAT', $user->roles, true);
    }
}

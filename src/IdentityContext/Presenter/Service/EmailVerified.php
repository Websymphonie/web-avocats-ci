<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Service;


use Websymphonie\IdentityContext\Domain\Exception\User\EmailAlreadyExistsException;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;

final readonly class EmailVerified
{
    public function __construct(private UserModelRepositoryInterface $repository)
    {
    }

    public function assertNotUsed(string $email): void
    {
        $used = $this->repository->getByEmail($email);
        if ($used !== null) {
            throw new EmailAlreadyExistsException($email);
        }
    }
}
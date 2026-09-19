<?php

declare(strict_types=1);

namespace Websymphonie\AuthContext\Presenter\Security;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AccountDisabledException extends AccountStatusException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey(): string
    {
        return 'Votre compte est désactivé.';
    }
}

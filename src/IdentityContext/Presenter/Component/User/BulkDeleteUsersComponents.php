<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Component\User;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('BulkDeleteUsers', template: 'identity/user/components/bulk_delete_users_component.html.twig')]
final class BulkDeleteUsersComponents
{
    public string $formId;
}

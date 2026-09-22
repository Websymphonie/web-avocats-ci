<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Presenter\Controller\Backoffice;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContactContext\Application\Usecase\Query\Message\GetContactMessageDetailsQuery;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/admin/contact/messages', name: 'contact_admin_message_')]
#[IsGranted('CONTACT_MESSAGE_VIEW')]
#[HasGroupAccess(RoleGroupEnum::CONTACT_MESSAGES)]
final class GetContactMessageDetailsController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/{uuid}', name: 'show', requirements: ['uuid' => '[0-9a-fA-F-]{36}'], methods: ['GET'])]
    public function __invoke(string $uuid): Response
    {
        $message = $this->handleQuery(new GetContactMessageDetailsQuery($uuid));

        return $this->render('contact/admin/message/show.html.twig', [
            'message' => $message,
        ]);
    }
}

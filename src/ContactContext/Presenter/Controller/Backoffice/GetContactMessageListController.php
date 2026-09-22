<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Presenter\Controller\Backoffice;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContactContext\Application\Usecase\Query\Message\GetContactMessageListQuery;
use Websymphonie\ContactContext\Domain\Enum\ContactMessageDeliveryStatus;
use Websymphonie\ContactContext\Presenter\Form\ContactMessageFilterType;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/admin/contact/messages', name: 'contact_admin_message_')]
#[IsGranted('CONTACT_MESSAGE_LIST')]
#[HasGroupAccess(RoleGroupEnum::CONTACT_MESSAGES)]
final class GetContactMessageListController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(Request $request, ContextServiceInterface $context): Response
    {
        $query = new GetContactMessageListQuery(page: max(1, $request->query->getInt('page', 1)));
        $form = $this->createForm(ContactMessageFilterType::class, $query, [
            'method' => 'GET',
            'action' => $this->generateUrl('contact_admin_message_list'),
        ]);
        $form->handleRequest($request);

        $messages = $this->handleQuery(new GetContactMessageListQuery(
            status: $query->status instanceof ContactMessageDeliveryStatus ? $query->status : null,
            page: $query->page,
            limit: $context->getPaginatorPageSize(),
        ));

        return $this->render('contact/admin/message/index.html.twig', [
            'messages' => $messages,
            'filterForm' => $form->createView(),
        ]);
    }
}

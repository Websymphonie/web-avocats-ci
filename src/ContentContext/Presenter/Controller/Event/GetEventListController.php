<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\Event;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Query\Event\GetEventListQuery;
use Websymphonie\ContentContext\Domain\Enum\EventFormat;
use Websymphonie\ContentContext\Domain\Enum\EventStatus;
use Websymphonie\ContentContext\Presenter\Form\Event\EventFilterType;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/events', name: 'content_admin_event_')]
#[IsGranted('CONTENT_EVENT_VIEW')]
#[HasGroupAccess(RoleGroupEnum::EVENTS)]
final class GetEventListController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(Request $request, ContextServiceInterface $context): Response
    {
        $query = new GetEventListQuery(page: max(1, $request->query->getInt('page', 1)));
        $form = $this->createForm(EventFilterType::class, $query, ['method' => 'GET', 'action' => $this->generateUrl('content_admin_event_list')]);
        $form->handleRequest($request);
        $limit = $context->getPaginatorPageSize();
        $result = $this->handleQuery(new GetEventListQuery($query->search ?: null, $query->status instanceof EventStatus ? $query->status : null, $query->format instanceof EventFormat ? $query->format : null, $query->categoryId ?: null, $query->tagId ?: null, $query->page, $limit));
        return $this->render('content/admin/event/index.html.twig', ['events' => $result, 'filterForm' => $form->createView()]);
    }
}

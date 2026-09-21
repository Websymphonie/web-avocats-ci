<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\EventCategory;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Query\EventCategory\GetEventCategoryListQuery;
use Websymphonie\ContentContext\Presenter\Form\EventCategory\EventCategoryFilterType;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/event-categories', name: 'content_admin_event_category_')]
#[IsGranted('CONTENT_EVENT_CATEGORY_VIEW')]
#[HasGroupAccess(RoleGroupEnum::CATEGORY_EVENTS)]
final class GetEventCategoryListController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(Request $request, ContextServiceInterface $context): Response
    {
        $query = new GetEventCategoryListQuery(page: max(1, $request->query->getInt('page', 1)));
        $form = $this->createForm(EventCategoryFilterType::class, $query, ['method' => 'GET', 'action' => $this->generateUrl('content_admin_event_category_list')]);
        $form->handleRequest($request);
        $limit = $context->getPaginatorPageSize();
        $result = $this->handleQuery(new GetEventCategoryListQuery($query->search ?: null, $query->page, $limit));
        return $this->render('content/admin/event_category/index.html.twig', ['categories' => $result, 'filterForm' => $form->createView()]);
    }
}

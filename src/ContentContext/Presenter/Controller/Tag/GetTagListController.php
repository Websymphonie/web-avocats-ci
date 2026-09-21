<?php
declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\Tag;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Query\Tag\GetTagListQuery;
use Websymphonie\ContentContext\Presenter\Form\Tag\TagFilterType;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/tags', name: 'content_admin_tag_')]
#[IsGranted('CONTENT_TAG_VIEW')]
#[HasGroupAccess(RoleGroupEnum::TAGS)]
final class GetTagListController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(Request $request, ContextServiceInterface $context): Response
    {
        $query = new GetTagListQuery(page: max(1, $request->query->getInt('page', 1)));
        $form = $this->createForm(TagFilterType::class, $query, ['method' => 'GET', 'action' => $this->generateUrl('content_admin_tag_list')]);
        $form->handleRequest($request);
        $limit = $context->getPaginatorPageSize();
        return $this->render('content/admin/tag/index.html.twig', ['tags' => $this->handleQuery(new GetTagListQuery($query->search ?: null, $query->page, $limit)), 'filterForm' => $form->createView()]);
    }
}

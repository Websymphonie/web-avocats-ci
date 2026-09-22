<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Controller\EditorialVideoCategory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Query\EditorialVideoCategory\GetEditorialVideoCategoryListQuery;
use Websymphonie\ContentContext\Presenter\Form\EditorialVideoCategory\EditorialVideoCategoryFilterType;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/video-categories', name: 'content_admin_video_category_')]
#[IsGranted('CONTENT_VIDEO_CATEGORY_VIEW')]
#[HasGroupAccess(RoleGroupEnum::CATEGORY_VIDEOS)]
final class GetEditorialVideoCategoryListController extends AbstractController { #[Route('', name: 'list', methods: ['GET'])] public function __invoke(Request $request, ContextServiceInterface $context): Response { $query = new GetEditorialVideoCategoryListQuery(page: max(1, $request->query->getInt('page', 1))); $form = $this->createForm(EditorialVideoCategoryFilterType::class, $query, ['method' => 'GET', 'action' => $this->generateUrl('content_admin_video_category_list')]); $form->handleRequest($request); $limit = $context->getPaginatorPageSize(); return $this->render('content/admin/editorial_video_category/index.html.twig', ['categories' => $this->handleQuery(new GetEditorialVideoCategoryListQuery($query->search ?: null, $query->page, $limit)), 'filterForm' => $form->createView()]); } }

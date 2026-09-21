<?php
declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\EditorialVideo;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Query\EditorialVideo\GetEditorialVideoListQuery;
use Websymphonie\ContentContext\Domain\Enum\EditorialVideoStatus;
use Websymphonie\ContentContext\Domain\Enum\VideoProvider;
use Websymphonie\ContentContext\Presenter\Form\EditorialVideo\EditorialVideoFilterType;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/videos', name: 'content_admin_video_')]
#[IsGranted('CONTENT_VIDEO_VIEW')]
#[HasGroupAccess(RoleGroupEnum::VIDEOS)]
final class GetEditorialVideoListController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(Request $request, ContextServiceInterface $context): Response
    {
        $query = new GetEditorialVideoListQuery(page: max(1, $request->query->getInt('page', 1)));
        $form = $this->createForm(EditorialVideoFilterType::class, $query, ['method' => 'GET', 'action' => $this->generateUrl('content_admin_video_list')]);
        $form->handleRequest($request);
        $limit = $context->getPaginatorPageSize();
        $result = $this->handleQuery(new GetEditorialVideoListQuery($query->search ?: null, $query->status instanceof EditorialVideoStatus ? $query->status : null, $query->provider instanceof VideoProvider ? $query->provider : null, $query->tagId ?: null, $query->page, $limit));
        return $this->render('content/admin/editorial_video/index.html.twig', ['videos' => $result, 'filterForm' => $form->createView()]);
    }
}

<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Controller\EditorialVideo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Query\EditorialVideo\GetEditorialVideoListQuery;
use Websymphonie\ContentContext\Domain\Enum\EditorialVideoStatus;
use Websymphonie\ContentContext\Domain\Enum\VideoProvider;
use Websymphonie\ContentContext\Presenter\Form\EditorialVideo\EditorialVideoFilterType;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/videos', name: 'content_admin_video_')]
#[IsGranted('CONTENT_VIDEO_VIEW')]
final class GetEditorialVideoListController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(Request $request): Response { $query = new GetEditorialVideoListQuery(page: max(1, $request->query->getInt('page', 1))); $form = $this->createForm(EditorialVideoFilterType::class, $query, ['method' => 'GET', 'action' => $this->generateUrl('content_admin_video_list')]); $form->handleRequest($request); $result = $this->handleQuery(new GetEditorialVideoListQuery($query->search ?: null, $query->status instanceof EditorialVideoStatus ? $query->status : null, $query->provider instanceof VideoProvider ? $query->provider : null, $query->tagId ?: null, $query->page, 20)); return $this->render('content/admin/editorial_video/index.html.twig', ['videos' => $result, 'filterForm' => $form->createView()]); }
}

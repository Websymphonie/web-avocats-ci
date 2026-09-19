<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\News;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Query\News\GetNewsListQuery;
use Websymphonie\ContentContext\Domain\Enum\NewsStatus;
use Websymphonie\ContentContext\Presenter\Form\News\NewsFilterType;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/news', name: 'content_admin_news_')]
#[IsGranted('CONTENT_NEWS_VIEW')]
final class GetNewsListController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $query = new GetNewsListQuery(page: max(1, $request->query->getInt('page', 1)));
        $form = $this->createForm(NewsFilterType::class, $query, ['method' => 'GET', 'action' => $this->generateUrl('content_admin_news_list')]);
        $form->handleRequest($request);
        $result = $this->handleQuery(new GetNewsListQuery($query->search ?: null, $query->status instanceof NewsStatus ? $query->status : null, $query->page, 20));

        return $this->render('content/admin/news/index.html.twig', ['news' => $result, 'filterForm' => $form->createView()]);
    }
}

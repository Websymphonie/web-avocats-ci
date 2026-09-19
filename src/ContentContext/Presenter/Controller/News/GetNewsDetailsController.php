<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\News;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Query\News\GetNewsDetailsQuery;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/news', name: 'content_admin_news_')]
#[IsGranted('CONTENT_NEWS_VIEW')]
final class GetNewsDetailsController extends AbstractController
{
    #[Route('/{id}', name: 'show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function __invoke(int $id): Response
    {
        return $this->render('content/admin/news/show.html.twig', ['news' => $this->handleQuery(new GetNewsDetailsQuery($id))]);
    }
}

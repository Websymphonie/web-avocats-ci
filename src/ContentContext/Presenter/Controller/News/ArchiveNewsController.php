<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\News;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\News\ArchiveNewsCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/news', name: 'content_admin_news_')]
#[IsGranted('CONTENT_NEWS_PUBLISH')]
#[HasGroupAccess(RoleGroupEnum::NEWS)]
final class ArchiveNewsController extends AbstractController
{
    #[Route('/{id}/archive', name: 'archive', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function __invoke(Request $request, int $id): Response
    {
        if (!$this->isCsrfTokenValid('news_archive_' . $id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
        try { $this->handleCommand(new ArchiveNewsCommand($id)); $this->flash()->success('Actualité archivée.'); }
        catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        return $this->redirectToRoute('content_admin_news_list');
    }
}

<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\Page;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Query\Page\GetPageQuery;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/pages', name: 'content_admin_page_')]
#[IsGranted('CONTENT_PAGE_VIEW')]
#[HasGroupAccess(RoleGroupEnum::PAGES)]
final class GetPageDetailsController extends AbstractController
{
    public function __construct(private readonly RichTextSanitizerInterface $sanitizer, private readonly MediaPublicUrlResolverInterface $mediaUrls) {}

    #[Route('/{id}', name: 'show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function __invoke(int $id): Response
    {
        $page = $this->handleQuery(new GetPageQuery($id));
        $coverUrls = $page->coverMediaId !== null ? $this->mediaUrls->resolveMany([$page->coverMediaId]) : [];
        return $this->render('content/admin/page/show.html.twig', ['page' => $page, 'safeContent' => $this->sanitizer->sanitize($page->content), 'coverUrl' => $coverUrls[$page->coverMediaId] ?? null]);
    }
}

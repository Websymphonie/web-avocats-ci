<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\DocumentPublication;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Query\Document\GetDocumentPublicationDetailsQuery;
use Websymphonie\MediaContext\Domain\Repository\StoredFileRepositoryInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/documents', name: 'content_admin_document_')]
#[IsGranted('CONTENT_DOCUMENT_VIEW')]
#[HasGroupAccess(RoleGroupEnum::DOCUMENTS)]
final class GetDocumentPublicationDetailsController extends AbstractController
{
    public function __construct(private readonly RichTextSanitizerInterface $sanitizer, private readonly StoredFileRepositoryInterface $files) {}
    #[Route('/{id}', name: 'show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function __invoke(int $id): Response
    {
        $document = $this->handleQuery(new GetDocumentPublicationDetailsQuery($id));
        return $this->render('content/admin/document/show.html.twig', ['document' => $document, 'file' => $this->files->getById($document->storedFileId), 'safeDescription' => $this->sanitizer->sanitize($document->description)]);
    }
}

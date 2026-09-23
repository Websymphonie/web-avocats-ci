<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\DocumentPublication;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Service\DocumentDownloadService;
use Websymphonie\ContentContext\Domain\Exception\DocumentPublicationNotFoundException;
use Websymphonie\MediaContext\Domain\Exception\StoredFileNotFoundException;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/documents', name: 'content_admin_document_')]
#[IsGranted('CONTENT_DOCUMENT_VIEW')]
#[HasGroupAccess(RoleGroupEnum::DOCUMENTS)]
final class DownloadDocumentPublicationController extends AbstractController
{
    public function __construct(private readonly DocumentDownloadService $downloads) {}
    #[Route('/{id}/download', name: 'download', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function __invoke(int $id): BinaryFileResponse
    {
        try { $download = $this->downloads->forId($id); } catch (DocumentPublicationNotFoundException|StoredFileNotFoundException) { throw $this->createNotFoundException('Fichier du document indisponible.'); }
        $response = new BinaryFileResponse($download->path);
        $response->headers->set('Content-Type', $download->mimeType);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $this->safeFilename($download->originalName));
        return $response;
    }
    private function safeFilename(string $name): string { $name = preg_replace('/[^A-Za-z0-9._ -]+/u', '_', basename($name)) ?: 'document'; return trim($name) !== '' ? trim($name) : 'document'; }
}

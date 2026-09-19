<?php

declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Controller;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\ContentContext\Application\Service\DocumentDownloadPolicy;
use Websymphonie\ContentContext\Application\Service\DocumentDownloadService;
use Websymphonie\ContentContext\Domain\Exception\DocumentPublicationNotFoundException;
use Websymphonie\ContentContext\Domain\Repository\DocumentPublicationRepositoryInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\MediaContext\Domain\Exception\StoredFileNotFoundException;

final class DocumentDownloadController extends AbstractController
{
    public function __construct(private readonly DocumentPublicationRepositoryInterface $documents, private readonly DocumentDownloadPolicy $policy, private readonly DocumentDownloadService $downloads) {}
    #[Route('/documents/{uuid}/download', name: 'content_document_download', requirements: ['uuid' => '[0-9a-fA-F-]+'], methods: ['GET'])]
    public function __invoke(string $uuid): BinaryFileResponse
    {
        try { $document = $this->documents->getByUuid($uuid); } catch (DocumentPublicationNotFoundException|StoredFileNotFoundException) { throw $this->createNotFoundException('Document introuvable.'); }
        if (!$this->policy->canDownloadExternally($document)) { throw $this->createAccessDeniedException('Ce document n’est pas accessible avec votre compte.'); }
        try { $download = $this->downloads->forUuid($uuid); } catch (DocumentPublicationNotFoundException|StoredFileNotFoundException) { throw $this->createNotFoundException('Fichier du document indisponible.'); }
        $response = new BinaryFileResponse($download->path);
        $response->headers->set('Content-Type', $download->mimeType);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $name = preg_replace('/[^A-Za-z0-9._ -]+/u', '_', basename($download->originalName)) ?: 'document';
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, trim($name) !== '' ? trim($name) : 'document');
        return $response;
    }
}

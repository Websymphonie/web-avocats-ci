<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\DocumentPublication;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\Document\UpdateDocumentPublicationCommand;
use Websymphonie\ContentContext\Application\Usecase\Query\Document\GetDocumentPublicationDetailsQuery;
use Websymphonie\ContentContext\Presenter\Form\Document\DocumentPublicationFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/documents', name: 'content_admin_document_')]
#[IsGranted('CONTENT_DOCUMENT_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::DOCUMENTS)]
final class UpdateDocumentPublicationController extends AbstractController
{
    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function __invoke(Request $request, int $id): Response
    {
        $document = $this->handleQuery(new GetDocumentPublicationDetailsQuery($id));
        $command = new UpdateDocumentPublicationCommand($document->id, $document->title, $document->description, $document->accessLevel, array_map(static fn ($tag): int => $tag->id, $document->tags));
        $form = $this->createForm(DocumentPublicationFormType::class, $command);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { try { $this->handleCommand($command); $this->flash()->success('Publication modifiée.'); return $this->redirectToRoute('content_admin_document_edit', ['id' => $id]); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } }
        return $this->render('content/admin/document/edit.html.twig', ['document' => $document, 'form' => $form->createView()]);
    }
}

<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\DocumentPublication;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\Document\CreateDocumentPublicationCommand;
use Websymphonie\ContentContext\Presenter\Form\Document\DocumentPublicationFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/documents', name: 'content_admin_document_')]
#[IsGranted('CONTENT_DOCUMENT_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::DOCUMENTS)]
final class CreateDocumentPublicationController extends AbstractController
{
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $command = new CreateDocumentPublicationCommand();
        $form = $this->createForm(DocumentPublicationFormType::class, $command, ['include_file' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { try { $document = $this->handleCommand($command); $this->flash()->success('Publication créée en brouillon.'); return $this->redirectToRoute('content_admin_document_edit', ['id' => $document->id]); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } }
        return $this->render('content/admin/document/create.html.twig', ['form' => $form->createView()]);
    }
}

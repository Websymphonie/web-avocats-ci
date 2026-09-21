<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\DocumentPublication;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Query\Document\GetDocumentPublicationListQuery;
use Websymphonie\ContentContext\Domain\Enum\DocumentAccessLevel;
use Websymphonie\ContentContext\Domain\Enum\DocumentStatus;
use Websymphonie\ContentContext\Presenter\Form\Document\DocumentPublicationFilterType;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/documents', name: 'content_admin_document_')]
#[IsGranted('CONTENT_DOCUMENT_VIEW')]
#[HasGroupAccess(RoleGroupEnum::DOCUMENTS)]
final class GetDocumentPublicationListController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(Request $request, ContextServiceInterface $context): Response
    {
        $query = new GetDocumentPublicationListQuery(page: max(1, $request->query->getInt('page', 1)));
        $form = $this->createForm(DocumentPublicationFilterType::class, $query, ['method' => 'GET', 'action' => $this->generateUrl('content_admin_document_list')]);
        $form->handleRequest($request);
        $limit = $context->getPaginatorPageSize();
        $documents = $this->handleQuery(new GetDocumentPublicationListQuery($query->search ?: null, $query->status instanceof DocumentStatus ? $query->status : null, $query->accessLevel instanceof DocumentAccessLevel ? $query->accessLevel : null, $query->tagId ?: null, $query->page, $limit));
        return $this->render('content/admin/document/index.html.twig', ['documents' => $documents, 'filterForm' => $form->createView()]);
    }
}

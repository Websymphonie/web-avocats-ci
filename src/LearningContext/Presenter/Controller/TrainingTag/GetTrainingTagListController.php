<?php
declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\TrainingTag;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Query\TrainingTag\GetTrainingTagListQuery;
use Websymphonie\LearningContext\Domain\Model\TrainingTagListResult;
use Websymphonie\LearningContext\Presenter\Form\Taxonomy\TrainingTagFilterType;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/tags', name: 'learning_admin_tag_')]
#[IsGranted('LEARNING_TAG_VIEW')]
final class GetTrainingTagListController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(Request $request, ContextServiceInterface $context): Response
    {
        $query = new GetTrainingTagListQuery(page: max(1, $request->query->getInt('page', 1)));
        $form = $this->createForm(TrainingTagFilterType::class, $query, ['method' => 'GET', 'action' => $this->generateUrl('learning_admin_tag_list')]);
        $form->handleRequest($request);
        $limit = $context->getPaginatorPageSize();
        /** @var TrainingTagListResult $tags */
        $tags = $this->handleQuery(new GetTrainingTagListQuery($query->search ?: null, $query->page, $limit));
        return $this->render('learning/admin/taxonomy/tag_index.html.twig', ['tags' => $tags, 'filterForm' => $form->createView()]);
    }
}

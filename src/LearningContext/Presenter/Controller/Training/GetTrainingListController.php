<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\Training;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Query\GetTrainingListQuery;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;
use Websymphonie\LearningContext\Presenter\Form\Training\TrainingFilterType;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings', name: 'learning_admin_training_')]
#[IsGranted('LEARNING_TRAINING_VIEW')]
final class GetTrainingListController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $mediaUrls) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $query = new GetTrainingListQuery(page: max(1, $request->query->getInt('page', 1)));
        $form = $this->createForm(TrainingFilterType::class, $query, ['method' => 'GET', 'action' => $this->generateUrl('learning_admin_training_list')]);
        $form->handleRequest($request);
        $result = $this->handleQuery(new GetTrainingListQuery(
            search: $query->search ?: null,
            status: $query->status instanceof TrainingStatus ? $query->status : null,
            visibility: $query->visibility instanceof TrainingVisibility ? $query->visibility : null,
            accessType: $query->accessType instanceof TrainingAccessType ? $query->accessType : null,
            categoryId: $query->categoryId,
            tagId: $query->tagId,
            page: $query->page,
            limit: 20,
        ));

        $mediaIds = array_values(array_filter(array_map(static fn ($training): ?int => $training->coverMediaId, $result->items)));

        return $this->render('learning/admin/training/index.html.twig', [
            'trainings' => $result,
            'filterForm' => $form->createView(),
            'mediaUrls' => $this->mediaUrls->resolveMany($mediaIds),
        ]);
    }
}

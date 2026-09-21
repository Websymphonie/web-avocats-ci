<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\Training;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\LearningContext\Application\Usecase\Query\GetTrainingListQuery;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;
use Websymphonie\LearningContext\Presenter\Form\Training\TrainingFilterType;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings', name: 'learning_admin_training_')]
#[IsGranted('LEARNING_TRAINING_VIEW')]
#[HasGroupAccess(RoleGroupEnum::TRAININGS)]
final class GetTrainingListController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $mediaUrls)
    {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('', name: 'list', methods: ['GET'])]
    #[Route('/lives', name: 'lives', defaults: ['trainingType' => 'LIVE'], methods: ['GET'])]
    public function __invoke(Request $request, ContextServiceInterface $context): Response
    {
        $routeType = $request->attributes->get('trainingType');
        $query = new GetTrainingListQuery(type: $routeType === 'LIVE' ? TrainingType::LIVE : null, page: max(1, $request->query->getInt('page', 1)));
        $action = $routeType === 'LIVE' ? 'learning_admin_training_lives' : 'learning_admin_training_list';
        $form = $this->createForm(TrainingFilterType::class, $query, ['method' => 'GET', 'action' => $this->generateUrl($action)]);
        $form->handleRequest($request);
        $limit = $context->getPaginatorPageSize();
        $result = $this->handleQuery(new GetTrainingListQuery(
            search: $query->search ?: null,
            status: $query->status instanceof TrainingStatus ? $query->status : null,
            visibility: $query->visibility instanceof TrainingVisibility ? $query->visibility : null,
            accessType: $query->accessType instanceof TrainingAccessType ? $query->accessType : null,
            type: $query->type instanceof TrainingType ? $query->type : null,
            categoryId: $query->categoryId,
            tagId: $query->tagId,
            page: $query->page,
            limit: $limit,
        ));

        $mediaIds = array_values(array_filter(array_map(static fn($training): ?int => $training->coverMediaId, $result->items)));

        return $this->render('learning/admin/training/index.html.twig', [
            'trainings' => $result,
            'filterForm' => $form->createView(),
            'mediaUrls' => $this->mediaUrls->resolveMany($mediaIds),
            'isLiveListing' => $routeType === 'LIVE',
        ]);
    }
}

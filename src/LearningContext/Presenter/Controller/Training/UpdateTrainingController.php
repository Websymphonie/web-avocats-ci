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
use Websymphonie\LearningContext\Application\Usecase\Command\UpdateTrainingCommand;
use Websymphonie\LearningContext\Application\Usecase\Query\GetCourseStructureQuery;
use Websymphonie\LearningContext\Application\Usecase\Query\GetTrainingDetailsQuery;
use Websymphonie\LearningContext\Domain\Enum\LiveDeliveryMode;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Presenter\Form\Training\TrainingFormType;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings', name: 'learning_admin_training_')]
#[IsGranted('LEARNING_TRAINING_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::TRAININGS)]
final class UpdateTrainingController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $mediaUrls)
    {
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function __invoke(Request $request, int $id): Response
    {
        $training = $this->handleQuery(new GetTrainingDetailsQuery($id));
        $command = new UpdateTrainingCommand(
            id: $training->id,
            title: $training->title,
            summary: $training->summary,
            description: $training->description,
            visibility: $training->visibility,
            accessType: $training->accessType,
            type: $training->type,
            categoryIds: $training->categoryIds,
            tagIds: $training->tagIds,
            startsAt: $training->liveDetails?->startsAt,
            endsAt: $training->liveDetails?->endsAt,
            deliveryMode: $training->liveDetails !== null ? $training->liveDetails->deliveryMode : LiveDeliveryMode::ONLINE,
            location: $training->liveDetails?->location,
            joinUrl: $training->liveDetails?->joinUrl,
        );
        $form = $this->createForm(TrainingFormType::class, $command);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handleCommand($command);
                $this->flash()->success('Formation modifiée.');
                return $this->redirectToRoute('learning_admin_training_list');
            } catch (UserFacingError $exception) {
                $this->flash()->errorFromException($exception);
            }
        }

        $mediaUrls = $training->coverMediaId !== null ? $this->mediaUrls->resolveMany([$training->coverMediaId]) : [];

        return $this->render('learning/admin/training/edit.html.twig', [
            'training' => $training,
            'form' => $form->createView(),
            'coverUrl' => $mediaUrls[$training->coverMediaId] ?? null,
            'structure' => $training->type === TrainingType::COURSE ? $this->handleQuery(new GetCourseStructureQuery($training->id)) : null,
        ]);
    }
}

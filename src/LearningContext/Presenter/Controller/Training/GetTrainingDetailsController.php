<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\Training;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\LearningContext\Application\Usecase\Query\GetTrainingDetailsQuery;
use Websymphonie\LearningContext\Application\Usecase\Query\GetCourseStructureQuery;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings', name: 'learning_admin_training_')]
#[IsGranted('LEARNING_TRAINING_VIEW')]
final class GetTrainingDetailsController extends AbstractController
{
    public function __construct(
        private readonly RichTextSanitizerInterface $sanitizer,
        private readonly MediaPublicUrlResolverInterface $mediaUrls,
    ) {}

    #[Route('/{id}', name: 'show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function __invoke(int $id): Response
    {
        $training = $this->handleQuery(new GetTrainingDetailsQuery($id));
        $mediaUrls = $training->coverMediaId !== null ? $this->mediaUrls->resolveMany([$training->coverMediaId]) : [];

        return $this->render('learning/admin/training/show.html.twig', [
            'training' => $training,
            'coverUrl' => $mediaUrls[$training->coverMediaId] ?? null,
            'safeDescription' => $this->sanitizer->sanitize($training->description),
            'structure' => $this->handleQuery(new GetCourseStructureQuery($training->id)),
        ]);
    }
}

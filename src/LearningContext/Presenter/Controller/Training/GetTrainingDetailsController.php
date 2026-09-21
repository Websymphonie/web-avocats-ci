<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\Training;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\LearningContext\Application\Usecase\Query\GetCourseStructureQuery;
use Websymphonie\LearningContext\Application\Usecase\Query\GetTrainingDetailsQuery;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Repository\EnrollmentRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingCategoryRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingTagRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings', name: 'learning_admin_training_')]
#[IsGranted('LEARNING_TRAINING_VIEW')]
#[HasGroupAccess(RoleGroupEnum::TRAININGS)]
final class GetTrainingDetailsController extends AbstractController
{
    public function __construct(
        private readonly RichTextSanitizerInterface          $sanitizer,
        private readonly MediaPublicUrlResolverInterface     $mediaUrls,
        private readonly EnrollmentRepositoryInterface       $enrollments,
        private readonly TrainingCategoryRepositoryInterface $categories,
        private readonly TrainingTagRepositoryInterface      $tags,
    )
    {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/{id}', name: 'show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function __invoke(int $id): Response
    {
        $training = $this->handleQuery(new GetTrainingDetailsQuery($id));
        $mediaUrls = $training->coverMediaId !== null ? $this->mediaUrls->resolveMany([$training->coverMediaId]) : [];
        $categoryNames = array_map(static fn($category): string => $category->name, $this->categories->findByIds($training->categoryIds));
        $tagNames = array_map(static fn($tag): string => $tag->name, $this->tags->findByIds($training->tagIds));

        return $this->render('learning/admin/training/show.html.twig', [
            'training' => $training,
            'coverUrl' => $mediaUrls[$training->coverMediaId] ?? null,
            'safeDescription' => $this->sanitizer->sanitize($training->description),
            'structure' => $training->type === TrainingType::COURSE ? $this->handleQuery(new GetCourseStructureQuery($training->id)) : null,
            'activeEnrollmentCount' => $this->enrollments->countActiveByTraining($training->id),
            'categoryNames' => $categoryNames,
            'tagNames' => $tagNames,
        ]);
    }
}

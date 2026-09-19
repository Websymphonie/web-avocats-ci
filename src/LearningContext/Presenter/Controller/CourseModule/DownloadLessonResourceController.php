<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\CourseModule;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Service\LearningResourceStorageInterface;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonResourceRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\MediaContext\Domain\Repository\StoredFileRepositoryInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings/{trainingId}/modules/{moduleId}/lessons/{lessonId}/resources', name: 'learning_admin_lesson_resource_')]
#[IsGranted('LEARNING_TRAINING_MANAGE')]
final class DownloadLessonResourceController extends AbstractController
{
    #[Route('/{resourceId}/download', name: 'download', requirements: ['trainingId' => '\\d+', 'moduleId' => '\\d+', 'lessonId' => '\\d+', 'resourceId' => '\\d+'], methods: ['GET'])]
    public function __invoke(int $trainingId, int $moduleId, int $lessonId, int $resourceId, TrainingRepositoryInterface $trainingRepository, CourseModuleRepositoryInterface $moduleRepository, LessonRepositoryInterface $lessonRepository, LessonResourceRepositoryInterface $resourceRepository, StoredFileRepositoryInterface $fileRepository, LearningResourceStorageInterface $storage, CourseStructureGuard $guard): BinaryFileResponse
    {
        $training = $trainingRepository->getById($trainingId);
        $guard->assertCourse($training);
        $module = $moduleRepository->getByIdForTraining($moduleId, $training->id);
        $lessonRepository->getByIdForModule($lessonId, $module->id);
        $resource = $resourceRepository->getByIdForLesson($resourceId, $lessonId);
        $file = $fileRepository->getById($resource->storedFileId);
        $path = $storage->locate($file);
        $response = new BinaryFileResponse($path);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Content-Type', $file->mimeType);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename($file->originalName));
        return $response;
    }
}

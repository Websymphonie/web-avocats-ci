<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\Member;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\LearningContext\Application\Service\LearningResourceStorageInterface;
use Websymphonie\LearningContext\Application\Service\TrainingAccessPolicyInterface;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonResourceRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\MediaContext\Domain\Repository\StoredFileRepositoryInterface;
use Websymphonie\MediaContext\Domain\Exception\StoredFileNotFoundException;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/espace/learning/resources/{resourceUuid}/download', name: 'learning_member_resource_download', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class DownloadAccessibleLessonResourceController extends AbstractController
{
    public function __invoke(string $resourceUuid, CurrentUserProvider $currentUser, LessonResourceRepositoryInterface $resources, LessonRepositoryInterface $lessons, CourseModuleRepositoryInterface $modules, TrainingRepositoryInterface $trainings, TrainingAccessPolicyInterface $policy, StoredFileRepositoryInterface $files, LearningResourceStorageInterface $storage, LoggerInterface $logger): BinaryFileResponse
    {
        $user = $currentUser->user();
        if ($user === null || $user->id === null) { throw $this->createAccessDeniedException(); }
        if (!Uuid::isValid($resourceUuid)) { throw $this->createNotFoundException(); }
        $resource = $resources->getByUuid($resourceUuid);
        $lesson = $lessons->getById($resource->lessonId);
        $module = $modules->getById($lesson->moduleId);
        $training = $trainings->getById($module->trainingId);
        $policy->assertCanAccess($training->id, $user->id);
        try {
            $file = $files->getById($resource->storedFileId);
            $path = $storage->locate($file);
        } catch (StoredFileNotFoundException $exception) {
            $logger->warning('Learning resource file is unavailable.', [
                'resource_uuid' => $resourceUuid,
                'training_id' => $training->id,
                'stored_file_id' => $resource->storedFileId,
                'exception' => $exception,
            ]);
            throw $this->createNotFoundException('La ressource pédagogique est indisponible.', $exception);
        }
        $response = new BinaryFileResponse($path);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Content-Type', $file->mimeType);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, preg_replace('/[^A-Za-z0-9._-]+/', '_', basename($file->originalName)) ?: 'resource');
        return $response;
    }
}

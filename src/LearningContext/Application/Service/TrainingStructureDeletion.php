<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Service;

use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Model\Training;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonResourceRepositoryInterface;

final readonly class TrainingStructureDeletion
{
    public function __construct(
        private CourseModuleRepositoryInterface $modules,
        private LessonRepositoryInterface $lessons,
        private LessonResourceRepositoryInterface $resources,
        private LearningResourceFileServiceInterface $files,
    ) {
    }

    public function deleteFor(Training $training): void
    {
        if ($training->type !== TrainingType::COURSE) {
            return;
        }

        foreach ($this->modules->listByTraining($training->id) as $module) {
            foreach ($this->lessons->listByModule($module->id) as $lesson) {
                foreach ($this->resources->listByLesson($lesson->id) as $resource) {
                    $this->resources->delete($resource);
                    $this->files->deleteIfOrphaned($resource->storedFileId);
                }

                $this->lessons->delete($lesson);
            }

            $this->modules->delete($module);
        }
    }
}

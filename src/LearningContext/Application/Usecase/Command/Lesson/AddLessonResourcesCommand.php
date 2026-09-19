<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Command\Lesson;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class AddLessonResourcesCommand
{
    /** @param list<UploadedFile> $files */
    public function __construct(public int $trainingId, public int $moduleId, public int $lessonId, public array $files) {}
}

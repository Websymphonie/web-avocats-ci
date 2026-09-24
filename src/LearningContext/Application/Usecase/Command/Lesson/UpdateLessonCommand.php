<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Command\Lesson;

use Websymphonie\LearningContext\Domain\Enum\VideoProvider;

final class UpdateLessonCommand
{
    /** @param list<\Symfony\Component\HttpFoundation\File\UploadedFile> $resourceFiles */
    public function __construct(
        public int $trainingId,
        public int $moduleId,
        public int $id,
        public string $title = '',
        public ?string $summary = null,
        public string $content = '',
        public VideoProvider $videoProvider = VideoProvider::YOUTUBE,
        public ?string $videoReference = null,
        public array $resourceFiles = [],
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Query;

final readonly class GetMemberCoursePlayerQuery
{
    public function __construct(
        public string $trainingUuid,
        public int $userId,
        public ?string $lessonUuid = null,
    ) {
    }
}

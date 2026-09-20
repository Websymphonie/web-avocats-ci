<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler;

use Websymphonie\LearningContext\Application\Service\TrainingLearnerEligibilityInterface;
use Websymphonie\LearningContext\Application\Usecase\Command\EnrollInFreeTrainingCommand;
use Websymphonie\LearningContext\Domain\Event\EnrollmentActivatedEvent;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentSource;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Exception\TrainingEnrollmentDeniedException;
use Websymphonie\LearningContext\Domain\Model\Enrollment;
use Websymphonie\LearningContext\Domain\Repository\EnrollmentRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventDispatcher;

final readonly class EnrollInFreeTrainingHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $trainings, private EnrollmentRepositoryInterface $enrollments, private TrainingLearnerEligibilityInterface $eligibility, private EventDispatcher $eventDispatcher) {}

    public function __invoke(EnrollInFreeTrainingCommand $command): void
    {
        $training = $this->trainings->getById($command->trainingId);
        if (!$this->eligibility->isEligible($command->userId) || $training->status !== TrainingStatus::PUBLISHED || $training->accessType !== TrainingAccessType::FREE) { throw new TrainingEnrollmentDeniedException(); }
        $enrollment = $this->enrollments->findByTrainingAndUser($training->id, $command->userId);
        if ($enrollment === null) {
            $enrollment = new Enrollment(0, '', $training->id, $command->userId);
            $enrollment->activate(EnrollmentSource::SELF_SERVICE);
            $enrollment = $this->enrollments->save($enrollment);
            $this->emitActivation($enrollment, $training->title);
            return;
        }
        if (!$enrollment->isActive()) {
            $enrollment->activate(EnrollmentSource::SELF_SERVICE);
            $enrollment = $this->enrollments->save($enrollment);
            $this->emitActivation($enrollment, $training->title);
        }
    }

    private function emitActivation(Enrollment $enrollment, string $trainingTitle): void
    {
        $enrollment->emitEvent(new EnrollmentActivatedEvent(
            enrollmentUuid: $enrollment->uuid,
            userId: $enrollment->userId,
            trainingId: $enrollment->trainingId,
            source: $enrollment->source->value,
            trainingTitle: $trainingTitle,
            activationReference: $enrollment->activatedAt?->format('Y-m-d\\TH:i:s.uP') ?? $enrollment->uuid,
        ));
        $this->eventDispatcher->dispatch($enrollment->releaseEvents());
    }
}

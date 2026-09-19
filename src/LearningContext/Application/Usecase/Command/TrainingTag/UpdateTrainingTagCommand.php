<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Application\Usecase\Command\TrainingTag;
final class UpdateTrainingTagCommand { public function __construct(public int $id, public string $name = '') {} }

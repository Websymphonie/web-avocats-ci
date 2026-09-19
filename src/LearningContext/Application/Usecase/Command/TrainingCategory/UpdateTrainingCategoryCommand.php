<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Application\Usecase\Command\TrainingCategory;
final class UpdateTrainingCategoryCommand { public function __construct(public int $id, public string $name = '') {} }

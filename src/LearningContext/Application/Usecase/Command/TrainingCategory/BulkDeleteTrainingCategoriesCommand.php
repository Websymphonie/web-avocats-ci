<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Application\Usecase\Command\TrainingCategory;
final readonly class BulkDeleteTrainingCategoriesCommand { /** @param list<int> $ids */ public function __construct(public array $ids) {} }

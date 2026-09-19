<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Application\Usecase\Command\TrainingTag;
final readonly class BulkDeleteTrainingTagsCommand { /** @param list<int> $ids */ public function __construct(public array $ids) {} }

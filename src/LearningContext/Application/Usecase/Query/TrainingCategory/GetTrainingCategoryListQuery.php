<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Application\Usecase\Query\TrainingCategory;
final class GetTrainingCategoryListQuery { public function __construct(public ?string $search = null, public int $page = 1, public int $limit = 20) {} }

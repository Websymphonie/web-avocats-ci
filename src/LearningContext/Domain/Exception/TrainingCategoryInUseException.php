<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Domain\Exception;
use DomainException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
final class TrainingCategoryInUseException extends DomainException implements UserFacingError { public static function withCount(int $count): self { return new self(sprintf('Cette catégorie est utilisée par %d formation(s) et ne peut pas être supprimée.', $count)); } public function translationId(): string { return 'exceptions.learning.training_category_in_use'; } public function translationDomain(): string { return 'learning_context'; } public function translationParameters(): array { return []; } }

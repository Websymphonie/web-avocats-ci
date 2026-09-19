<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Domain\Exception;
use DomainException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
final class TrainingTagInUseException extends DomainException implements UserFacingError { public static function withCount(int $count): self { return new self(sprintf('Ce tag est utilisé par %d formation(s) et ne peut pas être supprimé.', $count)); } public function translationId(): string { return 'exceptions.learning.training_tag_in_use'; } public function translationDomain(): string { return 'learning_context'; } public function translationParameters(): array { return []; } }

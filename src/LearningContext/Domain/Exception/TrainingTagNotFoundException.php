<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Domain\Exception;
use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
final class TrainingTagNotFoundException extends RuntimeException implements UserFacingError { public static function withId(int $id): self { return new self(sprintf('Le tag de formation #%d est introuvable.', $id)); } public function translationId(): string { return 'exceptions.learning.training_tag_not_found'; } public function translationDomain(): string { return 'learning_context'; } public function translationParameters(): array { return []; } }

<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Domain\Exception;
use DomainException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
final class TrainingTaxonomySlugAlreadyExistsException extends DomainException implements UserFacingError { public static function withSlug(string $slug): self { return new self(sprintf('Le slug « %s » est déjà utilisé dans cette taxonomie.', $slug)); } public function translationId(): string { return 'exceptions.learning.training_taxonomy_slug_already_exists'; } public function translationDomain(): string { return 'learning_context'; } public function translationParameters(): array { return []; } }

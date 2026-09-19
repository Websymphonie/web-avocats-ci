<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Domain\Exception;
use DomainException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
final class TrainingTaxonomyAssociationNotFoundException extends DomainException implements UserFacingError { public static function withTypeAndId(string $type, int $id): self { return new self(sprintf('%s #%d est introuvable.', $type, $id)); } public function translationId(): string { return 'exceptions.learning.training_taxonomy_association_not_found'; } public function translationDomain(): string { return 'learning_context'; } public function translationParameters(): array { return []; } }

<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Exception;

use DomainException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class EditorialVideoCategoryRequiredException extends DomainException implements UserFacingError
{
    public function __construct() { parent::__construct('Une catégorie est obligatoire pour une vidéo éditoriale.'); }
    public function translationId(): string { return 'exceptions.content.editorial_video_category_required'; }
    public function translationDomain(): string { return 'content_context'; }
    public function translationParameters(): array { return []; }
}

<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Service;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Websymphonie\ContentContext\Domain\Enum\DocumentAccessLevel;
use Websymphonie\ContentContext\Domain\Enum\DocumentStatus;
use Websymphonie\ContentContext\Domain\Model\DocumentPublication;

final readonly class DocumentDownloadPolicy
{
    public function __construct(private AuthorizationCheckerInterface $authorizationChecker) {}
    public function canDownloadExternally(DocumentPublication $document): bool
    {
        if ($document->status !== DocumentStatus::PUBLISHED) { return false; }
        return match ($document->accessLevel) {
            DocumentAccessLevel::PUBLIC => true,
            DocumentAccessLevel::MEMBER => $this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY'),
            DocumentAccessLevel::RESTRICTED => $this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') && $this->authorizationChecker->isGranted('CONTENT_DOCUMENT_RESTRICTED_DOWNLOAD'),
            DocumentAccessLevel::PRIVATE => false,
        };
    }
}

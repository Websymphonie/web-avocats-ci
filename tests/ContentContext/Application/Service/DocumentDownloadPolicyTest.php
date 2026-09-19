<?php

declare(strict_types=1);

namespace Websymphonie\Tests\ContentContext\Application\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Websymphonie\ContentContext\Application\Service\DocumentDownloadPolicy;
use Websymphonie\ContentContext\Domain\Enum\DocumentAccessLevel;
use Websymphonie\ContentContext\Domain\Model\DocumentPublication;

final class DocumentDownloadPolicyTest extends TestCase
{
    public function testPublicPublishedDocumentIsAvailableToAnonymousUsers(): void
    {
        $checker = $this->createMock(AuthorizationCheckerInterface::class);
        $policy = new DocumentDownloadPolicy($checker);
        self::assertTrue($policy->canDownloadExternally(new DocumentPublication(1, '', 'Public', 'public', '', 1, DocumentAccessLevel::PUBLIC, \Websymphonie\ContentContext\Domain\Enum\DocumentStatus::PUBLISHED)));
    }

    public function testPrivateDocumentIsNeverAvailableExternally(): void
    {
        $checker = $this->createMock(AuthorizationCheckerInterface::class);
        $policy = new DocumentDownloadPolicy($checker);
        self::assertFalse($policy->canDownloadExternally(new DocumentPublication(1, '', 'Private', 'private', '', 1, DocumentAccessLevel::PRIVATE, \Websymphonie\ContentContext\Domain\Enum\DocumentStatus::PUBLISHED)));
    }
}

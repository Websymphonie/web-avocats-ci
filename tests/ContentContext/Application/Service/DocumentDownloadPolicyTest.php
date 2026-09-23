<?php

declare(strict_types=1);

namespace Websymphonie\Tests\ContentContext\Application\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Websymphonie\ContentContext\Application\Service\DocumentDownloadPolicy;
use Websymphonie\ContentContext\Domain\Enum\DocumentAccessLevel;
use Websymphonie\ContentContext\Domain\Model\DocumentPublication;
use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\IdentityContext\Domain\Model\User\UserModel;

final class DocumentDownloadPolicyTest extends TestCase
{
    public function testPublicPublishedDocumentIsAvailableToAnonymousUsers(): void
    {
        $checker = $this->createMock(AuthorizationCheckerInterface::class);
        $policy = new DocumentDownloadPolicy($checker, $this->currentUserProvider(null));
        self::assertTrue($policy->canDownloadExternally(new DocumentPublication(1, '', 'Public', 'public', '', 1, DocumentAccessLevel::PUBLIC, \Websymphonie\ContentContext\Domain\Enum\DocumentStatus::PUBLISHED)));
    }

    public function testPrivateDocumentIsNeverAvailableExternally(): void
    {
        $checker = $this->createMock(AuthorizationCheckerInterface::class);
        $policy = new DocumentDownloadPolicy($checker, $this->currentUserProvider(null));
        self::assertFalse($policy->canDownloadExternally(new DocumentPublication(1, '', 'Private', 'private', '', 1, DocumentAccessLevel::PRIVATE, \Websymphonie\ContentContext\Domain\Enum\DocumentStatus::PUBLISHED)));
    }

    public function testEnabledLawyerCanDownloadPublishedLawyerDocument(): void
    {
        $checker = $this->createMock(AuthorizationCheckerInterface::class);
        $checker->method('isGranted')->with('IS_AUTHENTICATED_FULLY')->willReturn(true);
        $policy = new DocumentDownloadPolicy($checker, $this->currentUserProvider(new UserModel(enabled: true, roles: ['ROLE_AVOCAT'])));

        self::assertTrue($policy->canDownloadExternally($this->lawyerDocument()));
    }

    public function testDisabledLawyerCannotDownloadPublishedLawyerDocument(): void
    {
        $checker = $this->createMock(AuthorizationCheckerInterface::class);
        $checker->method('isGranted')->with('IS_AUTHENTICATED_FULLY')->willReturn(true);
        $policy = new DocumentDownloadPolicy($checker, $this->currentUserProvider(new UserModel(enabled: false, roles: ['ROLE_AVOCAT'])));

        self::assertFalse($policy->canDownloadExternally($this->lawyerDocument()));
    }

    public function testRegularAuthenticatedUserCannotDownloadPublishedLawyerDocument(): void
    {
        $checker = $this->createMock(AuthorizationCheckerInterface::class);
        $checker->method('isGranted')->with('IS_AUTHENTICATED_FULLY')->willReturn(true);
        $policy = new DocumentDownloadPolicy($checker, $this->currentUserProvider(new UserModel(enabled: true, roles: ['ROLE_USER'])));

        self::assertFalse($policy->canDownloadExternally($this->lawyerDocument()));
    }

    private function lawyerDocument(): DocumentPublication
    {
        return new DocumentPublication(1, '', 'Lawyer', 'lawyer', '', 1, DocumentAccessLevel::LAWYER, \Websymphonie\ContentContext\Domain\Enum\DocumentStatus::PUBLISHED);
    }

    private function currentUserProvider(?UserModel $user): CurrentUserProvider
    {
        $provider = $this->createMock(CurrentUserProvider::class);
        $provider->method('user')->willReturn($user);

        return $provider;
    }
}

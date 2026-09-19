<?php

declare(strict_types=1);

namespace Websymphonie\Tests\IdentityContext\Domain\Service\User;

use PHPUnit\Framework\TestCase;
use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\IdentityContext\Domain\Service\User\DefaultRolePermissions;

final class DefaultRolePermissionsTest extends TestCase
{
    public function testManagerReceivesTheExistingGenericManagementPermissions(): void
    {
        $permissions = DefaultRolePermissions::forRole(UserRolesEnum::ADMIN);

        self::assertSame([
            PermissionEnum::LIST,
            PermissionEnum::VIEW,
            PermissionEnum::CREATE,
            PermissionEnum::EDIT,
            PermissionEnum::PRINT,
            PermissionEnum::DELETE,
            PermissionEnum::CONTENT_NEWS_VIEW,
            PermissionEnum::CONTENT_NEWS_MANAGE,
            PermissionEnum::CONTENT_NEWS_PUBLISH,
            PermissionEnum::CONTENT_NEWS_DELETE,
            PermissionEnum::CONTENT_NEWS_CATEGORY_VIEW,
            PermissionEnum::CONTENT_NEWS_CATEGORY_MANAGE,
            PermissionEnum::CONTENT_NEWS_CATEGORY_DELETE,
            PermissionEnum::CONTENT_TAG_VIEW,
            PermissionEnum::CONTENT_TAG_MANAGE,
            PermissionEnum::CONTENT_TAG_DELETE,
            PermissionEnum::CONTENT_EVENT_VIEW,
            PermissionEnum::CONTENT_EVENT_MANAGE,
            PermissionEnum::CONTENT_EVENT_PUBLISH,
            PermissionEnum::CONTENT_EVENT_CANCEL,
            PermissionEnum::CONTENT_EVENT_DELETE,
            PermissionEnum::CONTENT_EVENT_CATEGORY_VIEW,
            PermissionEnum::CONTENT_EVENT_CATEGORY_MANAGE,
            PermissionEnum::CONTENT_EVENT_CATEGORY_DELETE,
            PermissionEnum::CONTENT_VIDEO_VIEW,
            PermissionEnum::CONTENT_VIDEO_MANAGE,
            PermissionEnum::CONTENT_VIDEO_PUBLISH,
            PermissionEnum::CONTENT_VIDEO_DELETE,
            PermissionEnum::CONTENT_GALLERY_VIEW,
            PermissionEnum::CONTENT_GALLERY_MANAGE,
            PermissionEnum::CONTENT_GALLERY_PUBLISH,
            PermissionEnum::CONTENT_GALLERY_DELETE,
            PermissionEnum::CONTENT_DOCUMENT_VIEW,
            PermissionEnum::CONTENT_DOCUMENT_MANAGE,
            PermissionEnum::CONTENT_DOCUMENT_PUBLISH,
            PermissionEnum::CONTENT_DOCUMENT_DELETE,
            PermissionEnum::CONTENT_DOCUMENT_RESTRICTED_DOWNLOAD,
            PermissionEnum::LEARNING_TRAINING_VIEW,
            PermissionEnum::LEARNING_TRAINING_MANAGE,
            PermissionEnum::LEARNING_TRAINING_PUBLISH,
            PermissionEnum::LEARNING_TRAINING_DELETE,
            PermissionEnum::LEARNING_ENROLLMENT_VIEW,
            PermissionEnum::LEARNING_ENROLLMENT_MANAGE,
            PermissionEnum::LEARNING_CATEGORY_VIEW,
            PermissionEnum::LEARNING_CATEGORY_MANAGE,
            PermissionEnum::LEARNING_CATEGORY_DELETE,
            PermissionEnum::LEARNING_TAG_VIEW,
            PermissionEnum::LEARNING_TAG_MANAGE,
            PermissionEnum::LEARNING_TAG_DELETE,
        ], $permissions);
    }

    public function testConfigurableRolesReceiveTheirExistingGenericPermissions(): void
    {
        self::assertSame([
            PermissionEnum::LIST,
            PermissionEnum::VIEW,
            PermissionEnum::CREATE,
            PermissionEnum::EDIT,
            PermissionEnum::PRINT,
            PermissionEnum::DELETE,
            PermissionEnum::CONTENT_NEWS_VIEW,
            PermissionEnum::CONTENT_NEWS_MANAGE,
            PermissionEnum::CONTENT_NEWS_PUBLISH,
            PermissionEnum::CONTENT_NEWS_DELETE,
            PermissionEnum::CONTENT_NEWS_CATEGORY_VIEW,
            PermissionEnum::CONTENT_NEWS_CATEGORY_MANAGE,
            PermissionEnum::CONTENT_NEWS_CATEGORY_DELETE,
            PermissionEnum::CONTENT_TAG_VIEW,
            PermissionEnum::CONTENT_TAG_MANAGE,
            PermissionEnum::CONTENT_TAG_DELETE,
            PermissionEnum::CONTENT_EVENT_VIEW,
            PermissionEnum::CONTENT_EVENT_MANAGE,
            PermissionEnum::CONTENT_EVENT_PUBLISH,
            PermissionEnum::CONTENT_EVENT_CANCEL,
            PermissionEnum::CONTENT_EVENT_DELETE,
            PermissionEnum::CONTENT_EVENT_CATEGORY_VIEW,
            PermissionEnum::CONTENT_EVENT_CATEGORY_MANAGE,
            PermissionEnum::CONTENT_EVENT_CATEGORY_DELETE,
            PermissionEnum::CONTENT_VIDEO_VIEW,
            PermissionEnum::CONTENT_VIDEO_MANAGE,
            PermissionEnum::CONTENT_VIDEO_PUBLISH,
            PermissionEnum::CONTENT_VIDEO_DELETE,
            PermissionEnum::CONTENT_GALLERY_VIEW,
            PermissionEnum::CONTENT_GALLERY_MANAGE,
            PermissionEnum::CONTENT_GALLERY_PUBLISH,
            PermissionEnum::CONTENT_GALLERY_DELETE,
            PermissionEnum::CONTENT_DOCUMENT_VIEW,
            PermissionEnum::CONTENT_DOCUMENT_MANAGE,
            PermissionEnum::CONTENT_DOCUMENT_PUBLISH,
            PermissionEnum::CONTENT_DOCUMENT_DELETE,
            PermissionEnum::CONTENT_DOCUMENT_RESTRICTED_DOWNLOAD,
            PermissionEnum::LEARNING_TRAINING_VIEW,
            PermissionEnum::LEARNING_TRAINING_MANAGE,
            PermissionEnum::LEARNING_TRAINING_PUBLISH,
            PermissionEnum::LEARNING_TRAINING_DELETE,
            PermissionEnum::LEARNING_ENROLLMENT_VIEW,
            PermissionEnum::LEARNING_ENROLLMENT_MANAGE,
            PermissionEnum::LEARNING_CATEGORY_VIEW,
            PermissionEnum::LEARNING_CATEGORY_MANAGE,
            PermissionEnum::LEARNING_CATEGORY_DELETE,
            PermissionEnum::LEARNING_TAG_VIEW,
            PermissionEnum::LEARNING_TAG_MANAGE,
            PermissionEnum::LEARNING_TAG_DELETE,
        ], DefaultRolePermissions::forRole(UserRolesEnum::ADMIN));
        self::assertSame([
            PermissionEnum::LIST,
            PermissionEnum::VIEW,
            PermissionEnum::CREATE,
            PermissionEnum::EDIT,
            PermissionEnum::PRINT,
        ], DefaultRolePermissions::forRole(UserRolesEnum::AVOCAT));
        self::assertSame([
            PermissionEnum::LIST,
            PermissionEnum::VIEW,
            PermissionEnum::CREATE,
            PermissionEnum::EDIT,
        ], DefaultRolePermissions::forRole(UserRolesEnum::USER));
    }

    public function testAvocatHasTheExistingGenericManagementPermissions(): void
    {
        $avocat = DefaultRolePermissions::forRole(UserRolesEnum::AVOCAT);
        foreach ([PermissionEnum::LIST, PermissionEnum::VIEW, PermissionEnum::CREATE, PermissionEnum::EDIT, PermissionEnum::PRINT] as $permission) {
            self::assertContains($permission, $avocat);
        }
    }

    /**
     * @dataProvider defaultPermissionMatrix
     * @param list<UserRolesEnum> $allowedRoles
     */
    public function testCurrentDefaultsMatchThePermissionMatrix(PermissionEnum $permission, array $allowedRoles): void
    {
        foreach (UserRolesEnum::configurableRoles() as $role) {
            $granted = in_array($permission, DefaultRolePermissions::forRole($role), true);
            self::assertSame(in_array($role, $allowedRoles, true), $granted, $role->value . ' / ' . $permission->value);
        }
    }

    /**
     * @return iterable<string, array{PermissionEnum, list<UserRolesEnum>}>
     */
    public static function defaultPermissionMatrix(): iterable
    {
        yield 'list' => [PermissionEnum::LIST, UserRolesEnum::configurableRoles()];
        yield 'view' => [PermissionEnum::VIEW, UserRolesEnum::configurableRoles()];
        yield 'create' => [PermissionEnum::CREATE, UserRolesEnum::configurableRoles()];
        yield 'edit' => [PermissionEnum::EDIT, UserRolesEnum::configurableRoles()];
        yield 'print' => [PermissionEnum::PRINT, [UserRolesEnum::ADMIN, UserRolesEnum::AVOCAT]];
        yield 'delete' => [PermissionEnum::DELETE, [UserRolesEnum::ADMIN]];
        yield 'role management' => [PermissionEnum::ROLE_MANAGE, []];
        yield 'news view' => [PermissionEnum::CONTENT_NEWS_VIEW, [UserRolesEnum::ADMIN]];
        yield 'news manage' => [PermissionEnum::CONTENT_NEWS_MANAGE, [UserRolesEnum::ADMIN]];
        yield 'news publish' => [PermissionEnum::CONTENT_NEWS_PUBLISH, [UserRolesEnum::ADMIN]];
        yield 'news delete' => [PermissionEnum::CONTENT_NEWS_DELETE, [UserRolesEnum::ADMIN]];
        yield 'news category view' => [PermissionEnum::CONTENT_NEWS_CATEGORY_VIEW, [UserRolesEnum::ADMIN]];
        yield 'news category manage' => [PermissionEnum::CONTENT_NEWS_CATEGORY_MANAGE, [UserRolesEnum::ADMIN]];
        yield 'news category delete' => [PermissionEnum::CONTENT_NEWS_CATEGORY_DELETE, [UserRolesEnum::ADMIN]];
        yield 'tag view' => [PermissionEnum::CONTENT_TAG_VIEW, [UserRolesEnum::ADMIN]];
        yield 'tag manage' => [PermissionEnum::CONTENT_TAG_MANAGE, [UserRolesEnum::ADMIN]];
        yield 'tag delete' => [PermissionEnum::CONTENT_TAG_DELETE, [UserRolesEnum::ADMIN]];
        yield 'event view' => [PermissionEnum::CONTENT_EVENT_VIEW, [UserRolesEnum::ADMIN]];
        yield 'event manage' => [PermissionEnum::CONTENT_EVENT_MANAGE, [UserRolesEnum::ADMIN]];
        yield 'event publish' => [PermissionEnum::CONTENT_EVENT_PUBLISH, [UserRolesEnum::ADMIN]];
        yield 'event cancel' => [PermissionEnum::CONTENT_EVENT_CANCEL, [UserRolesEnum::ADMIN]];
        yield 'event delete' => [PermissionEnum::CONTENT_EVENT_DELETE, [UserRolesEnum::ADMIN]];
        yield 'event category view' => [PermissionEnum::CONTENT_EVENT_CATEGORY_VIEW, [UserRolesEnum::ADMIN]];
        yield 'event category manage' => [PermissionEnum::CONTENT_EVENT_CATEGORY_MANAGE, [UserRolesEnum::ADMIN]];
        yield 'event category delete' => [PermissionEnum::CONTENT_EVENT_CATEGORY_DELETE, [UserRolesEnum::ADMIN]];
        yield 'video view' => [PermissionEnum::CONTENT_VIDEO_VIEW, [UserRolesEnum::ADMIN]];
        yield 'video manage' => [PermissionEnum::CONTENT_VIDEO_MANAGE, [UserRolesEnum::ADMIN]];
        yield 'video publish' => [PermissionEnum::CONTENT_VIDEO_PUBLISH, [UserRolesEnum::ADMIN]];
        yield 'video delete' => [PermissionEnum::CONTENT_VIDEO_DELETE, [UserRolesEnum::ADMIN]];
        yield 'gallery view' => [PermissionEnum::CONTENT_GALLERY_VIEW, [UserRolesEnum::ADMIN]];
        yield 'gallery manage' => [PermissionEnum::CONTENT_GALLERY_MANAGE, [UserRolesEnum::ADMIN]];
        yield 'gallery publish' => [PermissionEnum::CONTENT_GALLERY_PUBLISH, [UserRolesEnum::ADMIN]];
        yield 'gallery delete' => [PermissionEnum::CONTENT_GALLERY_DELETE, [UserRolesEnum::ADMIN]];
        yield 'document view' => [PermissionEnum::CONTENT_DOCUMENT_VIEW, [UserRolesEnum::ADMIN]];
        yield 'document manage' => [PermissionEnum::CONTENT_DOCUMENT_MANAGE, [UserRolesEnum::ADMIN]];
        yield 'document publish' => [PermissionEnum::CONTENT_DOCUMENT_PUBLISH, [UserRolesEnum::ADMIN]];
        yield 'document delete' => [PermissionEnum::CONTENT_DOCUMENT_DELETE, [UserRolesEnum::ADMIN]];
        yield 'restricted document download' => [PermissionEnum::CONTENT_DOCUMENT_RESTRICTED_DOWNLOAD, [UserRolesEnum::ADMIN]];
        yield 'training view' => [PermissionEnum::LEARNING_TRAINING_VIEW, [UserRolesEnum::ADMIN]];
        yield 'training manage' => [PermissionEnum::LEARNING_TRAINING_MANAGE, [UserRolesEnum::ADMIN]];
        yield 'training publish' => [PermissionEnum::LEARNING_TRAINING_PUBLISH, [UserRolesEnum::ADMIN]];
        yield 'training delete' => [PermissionEnum::LEARNING_TRAINING_DELETE, [UserRolesEnum::ADMIN]];
    }

    public function testSuperAdministratorHasEveryKnownPermission(): void
    {
        self::assertSame(PermissionEnum::cases(), DefaultRolePermissions::forRole(UserRolesEnum::SUPER_ADMIN));
    }
}

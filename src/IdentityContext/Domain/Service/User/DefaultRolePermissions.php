<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Service\User;

use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;

/**
 * Initial permissions used only when a role has no persisted configuration.
 */
final class DefaultRolePermissions
{
    /** @return list<PermissionEnum> */
    public static function forRole(UserRolesEnum $role): array
    {
        if ($role === UserRolesEnum::SUPER_ADMIN) {
            return PermissionEnum::cases();
        }

        $permissions = match ($role) {
            UserRolesEnum::ADMIN => [
                PermissionEnum::LIST,
                PermissionEnum::VIEW,
                PermissionEnum::CREATE,
                PermissionEnum::EDIT,
                PermissionEnum::PRINT,
                PermissionEnum::DELETE,
            ],
            UserRolesEnum::AVOCAT => [
                PermissionEnum::LIST,
                PermissionEnum::VIEW,
                PermissionEnum::CREATE,
                PermissionEnum::EDIT,
                PermissionEnum::PRINT,
            ],
            default => [
                PermissionEnum::LIST,
                PermissionEnum::VIEW,
                PermissionEnum::CREATE,
                PermissionEnum::EDIT,
            ],
        };

        foreach (self::businessDefaults() as $permission => $roles) {
            if (in_array($role, $roles, true)) {
                $permissions[] = PermissionEnum::from($permission);
            }
        }

        return $permissions;
    }

    /** @return array<string, list<UserRolesEnum>> */
    private static function businessDefaults(): array
    {
        return [
            PermissionEnum::CONTENT_NEWS_VIEW->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_NEWS_MANAGE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_NEWS_PUBLISH->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_NEWS_DELETE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_NEWS_CATEGORY_VIEW->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_NEWS_CATEGORY_MANAGE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_NEWS_CATEGORY_DELETE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_TAG_VIEW->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_TAG_MANAGE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_TAG_DELETE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_EVENT_VIEW->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_EVENT_MANAGE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_EVENT_PUBLISH->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_EVENT_CANCEL->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_EVENT_DELETE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_EVENT_CATEGORY_VIEW->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_EVENT_CATEGORY_MANAGE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_EVENT_CATEGORY_DELETE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_VIDEO_VIEW->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_VIDEO_MANAGE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_VIDEO_PUBLISH->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_VIDEO_DELETE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_VIDEO_CATEGORY_VIEW->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_VIDEO_CATEGORY_MANAGE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_VIDEO_CATEGORY_DELETE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_GALLERY_VIEW->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_GALLERY_MANAGE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_GALLERY_PUBLISH->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_GALLERY_DELETE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_DOCUMENT_VIEW->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_DOCUMENT_MANAGE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_DOCUMENT_PUBLISH->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_DOCUMENT_DELETE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_DOCUMENT_RESTRICTED_DOWNLOAD->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_PAGE_VIEW->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_PAGE_MANAGE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_PAGE_PUBLISH->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTENT_PAGE_DELETE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::BATONNIER_LIST->value => [UserRolesEnum::ADMIN],
            PermissionEnum::BATONNIER_VIEW->value => [UserRolesEnum::ADMIN],
            PermissionEnum::BATONNIER_CREATE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::BATONNIER_EDIT->value => [UserRolesEnum::ADMIN],
            PermissionEnum::COUNCIL_MEMBER_LIST->value => [UserRolesEnum::ADMIN],
            PermissionEnum::COUNCIL_MEMBER_VIEW->value => [UserRolesEnum::ADMIN],
            PermissionEnum::COUNCIL_MEMBER_CREATE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::COUNCIL_MEMBER_EDIT->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CABINET_VIEW->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CABINET_CREATE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CABINET_EDIT->value => [UserRolesEnum::ADMIN],
            PermissionEnum::LEARNING_TRAINING_VIEW->value => [UserRolesEnum::ADMIN],
            PermissionEnum::LEARNING_TRAINING_MANAGE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::LEARNING_TRAINING_PUBLISH->value => [UserRolesEnum::ADMIN],
            PermissionEnum::LEARNING_TRAINING_DELETE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::LEARNING_ENROLLMENT_VIEW->value => [UserRolesEnum::ADMIN],
            PermissionEnum::LEARNING_ENROLLMENT_MANAGE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::LEARNING_CATEGORY_VIEW->value => [UserRolesEnum::ADMIN],
            PermissionEnum::LEARNING_CATEGORY_MANAGE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::LEARNING_CATEGORY_DELETE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::LEARNING_TAG_VIEW->value => [UserRolesEnum::ADMIN],
            PermissionEnum::LEARNING_TAG_MANAGE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::LEARNING_TAG_DELETE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::PAYMENT_VIEW->value => [UserRolesEnum::ADMIN],
            PermissionEnum::PAYMENT_OFFER_VIEW->value => [UserRolesEnum::ADMIN],
            PermissionEnum::PAYMENT_OFFER_MANAGE->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTACT_MESSAGE_LIST->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTACT_MESSAGE_VIEW->value => [UserRolesEnum::ADMIN],
            PermissionEnum::CONTACT_MESSAGE_RETRY->value => [UserRolesEnum::ADMIN],
        ];
    }
}

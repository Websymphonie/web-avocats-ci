<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Service\Role;

use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;

final class PermissionCatalog
{
    /** @return array<string, array<string, string>> */
    public static function groupedChoices(): array
    {
        $groups = [];
        foreach (PermissionEnum::configurableCases() as $permission) {
            $groups[self::group($permission)][self::label($permission)] = $permission->value;
        }

        return $groups;
    }

    private static function group(PermissionEnum $permission): string
    {
        return match ($permission) {
            PermissionEnum::LIST,
            PermissionEnum::VIEW,
            PermissionEnum::CREATE,
            PermissionEnum::EDIT,
            PermissionEnum::DELETE,
            PermissionEnum::PRINT => 'Administration',
            PermissionEnum::ROLE_MANAGE => 'Administration',
            PermissionEnum::CONTENT_NEWS_VIEW,
            PermissionEnum::CONTENT_NEWS_MANAGE,
            PermissionEnum::CONTENT_NEWS_PUBLISH,
            PermissionEnum::CONTENT_NEWS_DELETE => 'Contenu · Actualités',
            PermissionEnum::CONTENT_NEWS_CATEGORY_VIEW,
            PermissionEnum::CONTENT_NEWS_CATEGORY_MANAGE,
            PermissionEnum::CONTENT_NEWS_CATEGORY_DELETE => 'Contenu · Catégories d’actualités',
            PermissionEnum::CONTENT_TAG_VIEW,
            PermissionEnum::CONTENT_TAG_MANAGE,
            PermissionEnum::CONTENT_TAG_DELETE => 'Contenu · Tags',
            PermissionEnum::CONTENT_EVENT_VIEW,
            PermissionEnum::CONTENT_EVENT_MANAGE,
            PermissionEnum::CONTENT_EVENT_PUBLISH,
            PermissionEnum::CONTENT_EVENT_CANCEL,
            PermissionEnum::CONTENT_EVENT_DELETE => 'Contenu · Événements',
            PermissionEnum::CONTENT_EVENT_CATEGORY_VIEW,
            PermissionEnum::CONTENT_EVENT_CATEGORY_MANAGE,
            PermissionEnum::CONTENT_EVENT_CATEGORY_DELETE => 'Contenu · Catégories d’événements',
            PermissionEnum::CONTENT_VIDEO_VIEW,
            PermissionEnum::CONTENT_VIDEO_MANAGE,
            PermissionEnum::CONTENT_VIDEO_PUBLISH,
            PermissionEnum::CONTENT_VIDEO_DELETE => 'Contenu · Vidéos éditoriales',
            PermissionEnum::CONTENT_GALLERY_VIEW,
            PermissionEnum::CONTENT_GALLERY_MANAGE,
            PermissionEnum::CONTENT_GALLERY_PUBLISH,
            PermissionEnum::CONTENT_GALLERY_DELETE => 'Contenu · Galeries photos',
            PermissionEnum::CONTENT_DOCUMENT_VIEW,
            PermissionEnum::CONTENT_DOCUMENT_MANAGE,
            PermissionEnum::CONTENT_DOCUMENT_PUBLISH,
            PermissionEnum::CONTENT_DOCUMENT_DELETE,
            PermissionEnum::CONTENT_DOCUMENT_RESTRICTED_DOWNLOAD => 'Contenu · Documents',
            PermissionEnum::CONTENT_PAGE_VIEW,
            PermissionEnum::CONTENT_PAGE_MANAGE,
            PermissionEnum::CONTENT_PAGE_PUBLISH,
            PermissionEnum::CONTENT_PAGE_DELETE => 'Contenu · Pages statiques',
            PermissionEnum::LEARNING_TRAINING_VIEW,
            PermissionEnum::LEARNING_TRAINING_MANAGE,
            PermissionEnum::LEARNING_TRAINING_PUBLISH,
            PermissionEnum::LEARNING_TRAINING_DELETE => 'Formations · Formations',
            PermissionEnum::LEARNING_ENROLLMENT_VIEW,
            PermissionEnum::LEARNING_ENROLLMENT_MANAGE => 'Formations · Inscriptions',
            PermissionEnum::LEARNING_CATEGORY_VIEW,
            PermissionEnum::LEARNING_CATEGORY_MANAGE,
            PermissionEnum::LEARNING_CATEGORY_DELETE => 'Formations · Catégories',
            PermissionEnum::LEARNING_TAG_VIEW,
            PermissionEnum::LEARNING_TAG_MANAGE,
            PermissionEnum::LEARNING_TAG_DELETE => 'Formations · Tags',
            PermissionEnum::PAYMENT_VIEW,
            PermissionEnum::PAYMENT_OFFER_VIEW,
            PermissionEnum::PAYMENT_OFFER_MANAGE => 'Paiements',
        };
    }

    private static function label(PermissionEnum $permission): string
    {
        return $permission->label();
    }
}

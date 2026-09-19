<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Enum;

use Websymphonie\SharedContext\Domain\Enum\ColorEnum;

enum PermissionEnum: string
{
    case LIST = 'ROLE_LIST';
    case VIEW = 'ROLE_VIEW';
    case CREATE = 'ROLE_CREATE';
    case EDIT = 'ROLE_EDIT';
    case DELETE = 'ROLE_DELETE';
    case PRINT = 'ROLE_PRINT';
    case ROLE_MANAGE = 'ROLE_MANAGE';
    case CONTENT_NEWS_VIEW = 'CONTENT_NEWS_VIEW';
    case CONTENT_NEWS_MANAGE = 'CONTENT_NEWS_MANAGE';
    case CONTENT_NEWS_PUBLISH = 'CONTENT_NEWS_PUBLISH';
    case CONTENT_NEWS_DELETE = 'CONTENT_NEWS_DELETE';
    case CONTENT_NEWS_CATEGORY_VIEW = 'CONTENT_NEWS_CATEGORY_VIEW';
    case CONTENT_NEWS_CATEGORY_MANAGE = 'CONTENT_NEWS_CATEGORY_MANAGE';
    case CONTENT_NEWS_CATEGORY_DELETE = 'CONTENT_NEWS_CATEGORY_DELETE';
    case CONTENT_TAG_VIEW = 'CONTENT_TAG_VIEW';
    case CONTENT_TAG_MANAGE = 'CONTENT_TAG_MANAGE';
    case CONTENT_TAG_DELETE = 'CONTENT_TAG_DELETE';
    case CONTENT_EVENT_VIEW = 'CONTENT_EVENT_VIEW';
    case CONTENT_EVENT_MANAGE = 'CONTENT_EVENT_MANAGE';
    case CONTENT_EVENT_PUBLISH = 'CONTENT_EVENT_PUBLISH';
    case CONTENT_EVENT_CANCEL = 'CONTENT_EVENT_CANCEL';
    case CONTENT_EVENT_DELETE = 'CONTENT_EVENT_DELETE';
    case CONTENT_EVENT_CATEGORY_VIEW = 'CONTENT_EVENT_CATEGORY_VIEW';
    case CONTENT_EVENT_CATEGORY_MANAGE = 'CONTENT_EVENT_CATEGORY_MANAGE';
    case CONTENT_EVENT_CATEGORY_DELETE = 'CONTENT_EVENT_CATEGORY_DELETE';
    case CONTENT_VIDEO_VIEW = 'CONTENT_VIDEO_VIEW';
    case CONTENT_VIDEO_MANAGE = 'CONTENT_VIDEO_MANAGE';
    case CONTENT_VIDEO_PUBLISH = 'CONTENT_VIDEO_PUBLISH';
    case CONTENT_VIDEO_DELETE = 'CONTENT_VIDEO_DELETE';
    case CONTENT_GALLERY_VIEW = 'CONTENT_GALLERY_VIEW';
    case CONTENT_GALLERY_MANAGE = 'CONTENT_GALLERY_MANAGE';
    case CONTENT_GALLERY_PUBLISH = 'CONTENT_GALLERY_PUBLISH';
    case CONTENT_GALLERY_DELETE = 'CONTENT_GALLERY_DELETE';
    case CONTENT_DOCUMENT_VIEW = 'CONTENT_DOCUMENT_VIEW';
    case CONTENT_DOCUMENT_MANAGE = 'CONTENT_DOCUMENT_MANAGE';
    case CONTENT_DOCUMENT_PUBLISH = 'CONTENT_DOCUMENT_PUBLISH';
    case CONTENT_DOCUMENT_DELETE = 'CONTENT_DOCUMENT_DELETE';
    case CONTENT_DOCUMENT_RESTRICTED_DOWNLOAD = 'CONTENT_DOCUMENT_RESTRICTED_DOWNLOAD';

    /** @return list<self> */
    public static function configurableCases(): array
    {
        return [
            self::LIST,
            self::VIEW,
            self::CREATE,
            self::EDIT,
            self::DELETE,
            self::PRINT,
            self::CONTENT_NEWS_VIEW,
            self::CONTENT_NEWS_MANAGE,
            self::CONTENT_NEWS_PUBLISH,
            self::CONTENT_NEWS_DELETE,
            self::CONTENT_NEWS_CATEGORY_VIEW,
            self::CONTENT_NEWS_CATEGORY_MANAGE,
            self::CONTENT_NEWS_CATEGORY_DELETE,
            self::CONTENT_TAG_VIEW,
            self::CONTENT_TAG_MANAGE,
            self::CONTENT_TAG_DELETE,
            self::CONTENT_EVENT_VIEW,
            self::CONTENT_EVENT_MANAGE,
            self::CONTENT_EVENT_PUBLISH,
            self::CONTENT_EVENT_CANCEL,
            self::CONTENT_EVENT_DELETE,
            self::CONTENT_EVENT_CATEGORY_VIEW,
            self::CONTENT_EVENT_CATEGORY_MANAGE,
            self::CONTENT_EVENT_CATEGORY_DELETE,
            self::CONTENT_VIDEO_VIEW,
            self::CONTENT_VIDEO_MANAGE,
            self::CONTENT_VIDEO_PUBLISH,
            self::CONTENT_VIDEO_DELETE,
            self::CONTENT_GALLERY_VIEW,
            self::CONTENT_GALLERY_MANAGE,
            self::CONTENT_GALLERY_PUBLISH,
            self::CONTENT_GALLERY_DELETE,
            self::CONTENT_DOCUMENT_VIEW,
            self::CONTENT_DOCUMENT_MANAGE,
            self::CONTENT_DOCUMENT_PUBLISH,
            self::CONTENT_DOCUMENT_DELETE,
            self::CONTENT_DOCUMENT_RESTRICTED_DOWNLOAD,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::LIST => 'Lecture (liste)',
            self::VIEW => 'Lecture (détail)',
            self::CREATE => 'Création',
            self::EDIT => 'Édition',
            self::DELETE => 'Suppression',
            self::PRINT => 'Impression',
            self::ROLE_MANAGE => 'Rôles : configuration de la matrice des permissions',
            self::CONTENT_NEWS_VIEW => 'Actualités : lecture',
            self::CONTENT_NEWS_MANAGE => 'Actualités : création et édition',
            self::CONTENT_NEWS_PUBLISH => 'Actualités : publication et archivage',
            self::CONTENT_NEWS_DELETE => 'Actualités : suppression',
            self::CONTENT_NEWS_CATEGORY_VIEW => 'Catégories d’actualités : lecture',
            self::CONTENT_NEWS_CATEGORY_MANAGE => 'Catégories d’actualités : création et édition',
            self::CONTENT_NEWS_CATEGORY_DELETE => 'Catégories d’actualités : suppression',
            self::CONTENT_TAG_VIEW => 'Tags : lecture',
            self::CONTENT_TAG_MANAGE => 'Tags : création et édition',
            self::CONTENT_TAG_DELETE => 'Tags : suppression',
            self::CONTENT_EVENT_VIEW => 'Événements : lecture',
            self::CONTENT_EVENT_MANAGE => 'Événements : création et édition',
            self::CONTENT_EVENT_PUBLISH => 'Événements : publication et archivage',
            self::CONTENT_EVENT_CANCEL => 'Événements : annulation',
            self::CONTENT_EVENT_DELETE => 'Événements : suppression',
            self::CONTENT_EVENT_CATEGORY_VIEW => 'Catégories d’événements : lecture',
            self::CONTENT_EVENT_CATEGORY_MANAGE => 'Catégories d’événements : création et édition',
            self::CONTENT_EVENT_CATEGORY_DELETE => 'Catégories d’événements : suppression',
            self::CONTENT_VIDEO_VIEW => 'Vidéos éditoriales : lecture',
            self::CONTENT_VIDEO_MANAGE => 'Vidéos éditoriales : création et édition',
            self::CONTENT_VIDEO_PUBLISH => 'Vidéos éditoriales : publication et archivage',
            self::CONTENT_VIDEO_DELETE => 'Vidéos éditoriales : suppression',
            self::CONTENT_GALLERY_VIEW => 'Galeries photos : lecture',
            self::CONTENT_GALLERY_MANAGE => 'Galeries photos : création et édition',
            self::CONTENT_GALLERY_PUBLISH => 'Galeries photos : publication et archivage',
            self::CONTENT_GALLERY_DELETE => 'Galeries photos : suppression',
            self::CONTENT_DOCUMENT_VIEW => 'Documents : lecture',
            self::CONTENT_DOCUMENT_MANAGE => 'Documents : création et édition',
            self::CONTENT_DOCUMENT_PUBLISH => 'Documents : publication et archivage',
            self::CONTENT_DOCUMENT_DELETE => 'Documents : suppression',
            self::CONTENT_DOCUMENT_RESTRICTED_DOWNLOAD => 'Documents : téléchargement restreint',
        };
    }

    public function badge(): ColorEnum
    {
        return match ($this) {
            self::DELETE => ColorEnum::DANGER,
            self::CREATE => ColorEnum::SUCCESS,
            self::EDIT => ColorEnum::WARNING,
            self::LIST => ColorEnum::PRIMARY,
            self::VIEW => ColorEnum::INFO,
            self::PRINT => ColorEnum::SECONDARY,
            self::ROLE_MANAGE => ColorEnum::PRIMARY,
            self::CONTENT_NEWS_VIEW => ColorEnum::INFO,
            self::CONTENT_NEWS_MANAGE => ColorEnum::SUCCESS,
            self::CONTENT_NEWS_PUBLISH => ColorEnum::WARNING,
            self::CONTENT_NEWS_DELETE => ColorEnum::DANGER,
            self::CONTENT_NEWS_CATEGORY_VIEW => ColorEnum::INFO,
            self::CONTENT_NEWS_CATEGORY_MANAGE => ColorEnum::SUCCESS,
            self::CONTENT_NEWS_CATEGORY_DELETE => ColorEnum::DANGER,
            self::CONTENT_TAG_VIEW => ColorEnum::INFO,
            self::CONTENT_TAG_MANAGE => ColorEnum::SUCCESS,
            self::CONTENT_TAG_DELETE => ColorEnum::DANGER,
            self::CONTENT_EVENT_VIEW => ColorEnum::INFO,
            self::CONTENT_EVENT_MANAGE => ColorEnum::SUCCESS,
            self::CONTENT_EVENT_PUBLISH => ColorEnum::WARNING,
            self::CONTENT_EVENT_CANCEL => ColorEnum::WARNING,
            self::CONTENT_EVENT_DELETE => ColorEnum::DANGER,
            self::CONTENT_EVENT_CATEGORY_VIEW => ColorEnum::INFO,
            self::CONTENT_EVENT_CATEGORY_MANAGE => ColorEnum::SUCCESS,
            self::CONTENT_EVENT_CATEGORY_DELETE => ColorEnum::DANGER,
            self::CONTENT_VIDEO_VIEW => ColorEnum::INFO,
            self::CONTENT_VIDEO_MANAGE => ColorEnum::SUCCESS,
            self::CONTENT_VIDEO_PUBLISH => ColorEnum::WARNING,
            self::CONTENT_VIDEO_DELETE => ColorEnum::DANGER,
            self::CONTENT_GALLERY_VIEW => ColorEnum::INFO,
            self::CONTENT_GALLERY_MANAGE => ColorEnum::SUCCESS,
            self::CONTENT_GALLERY_PUBLISH => ColorEnum::WARNING,
            self::CONTENT_GALLERY_DELETE => ColorEnum::DANGER,
            self::CONTENT_DOCUMENT_VIEW => ColorEnum::INFO,
            self::CONTENT_DOCUMENT_MANAGE => ColorEnum::SUCCESS,
            self::CONTENT_DOCUMENT_PUBLISH => ColorEnum::WARNING,
            self::CONTENT_DOCUMENT_DELETE => ColorEnum::DANGER,
            self::CONTENT_DOCUMENT_RESTRICTED_DOWNLOAD => ColorEnum::WARNING,
        };
    }
}

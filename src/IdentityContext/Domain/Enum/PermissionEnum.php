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
        };
    }
}

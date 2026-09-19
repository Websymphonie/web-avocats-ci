<?php
declare(strict_types=1);
namespace Websymphonie\SharedContext\Application\Service\Sidebar\Modules;
use Websymphonie\SharedContext\Application\Service\Sidebar\Enum\RouteEnum;
use Websymphonie\SharedContext\Application\Service\Sidebar\Model\MenuFactory;
use Websymphonie\SharedContext\Application\Service\Sidebar\Service\SidebarModuleInterface;
final class ContentMenu implements SidebarModuleInterface
{
    public const string GROUP = 'Contenu';
    public static function items(): array
    {
        return [
            MenuFactory::item('Actualités', [RouteEnum::CONTENT_NEWS_INDEX->value, 'content_admin_news_new', 'content_admin_news_show', 'content_admin_news_edit'], [], 'lucide:newspaper', RouteEnum::CONTENT_NEWS_INDEX->value, self::GROUP, permission: 'CONTENT_NEWS_VIEW', groupOrder: 2, order: 1),
            MenuFactory::item('Catégories d’actualités', [RouteEnum::CONTENT_NEWS_CATEGORY_INDEX->value, 'content_admin_news_category_new', 'content_admin_news_category_edit'], [], 'lucide:folders', RouteEnum::CONTENT_NEWS_CATEGORY_INDEX->value, self::GROUP, permission: 'CONTENT_NEWS_CATEGORY_VIEW', groupOrder: 2, order: 2),
            MenuFactory::item('Événements', [RouteEnum::CONTENT_EVENT_INDEX->value, 'content_admin_event_new', 'content_admin_event_show', 'content_admin_event_edit'], [], 'lucide:calendar-days', RouteEnum::CONTENT_EVENT_INDEX->value, self::GROUP, permission: 'CONTENT_EVENT_VIEW', groupOrder: 2, order: 3),
            MenuFactory::item('Catégories d’événements', [RouteEnum::CONTENT_EVENT_CATEGORY_INDEX->value, 'content_admin_event_category_new', 'content_admin_event_category_edit'], [], 'lucide:folders', RouteEnum::CONTENT_EVENT_CATEGORY_INDEX->value, self::GROUP, permission: 'CONTENT_EVENT_CATEGORY_VIEW', groupOrder: 2, order: 4),
            MenuFactory::item('Vidéos', [RouteEnum::CONTENT_VIDEO_INDEX->value, 'content_admin_video_new', 'content_admin_video_show', 'content_admin_video_edit'], [], 'lucide:clapperboard', RouteEnum::CONTENT_VIDEO_INDEX->value, self::GROUP, permission: 'CONTENT_VIDEO_VIEW', groupOrder: 2, order: 5),
            MenuFactory::item('Galeries photos', [RouteEnum::CONTENT_GALLERY_INDEX->value, 'content_admin_gallery_new', 'content_admin_gallery_show', 'content_admin_gallery_edit'], [], 'lucide:images', RouteEnum::CONTENT_GALLERY_INDEX->value, self::GROUP, permission: 'CONTENT_GALLERY_VIEW', groupOrder: 2, order: 6),
            MenuFactory::item('Tags', [RouteEnum::CONTENT_TAG_INDEX->value, 'content_admin_tag_new', 'content_admin_tag_edit'], [], 'lucide:tags', RouteEnum::CONTENT_TAG_INDEX->value, self::GROUP, permission: 'CONTENT_TAG_VIEW', groupOrder: 2, order: 7),
        ];
    }
}

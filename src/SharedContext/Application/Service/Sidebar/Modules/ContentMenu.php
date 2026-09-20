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
            MenuFactory::item(
                label: 'Actualités',
                routes: [
                    RouteEnum::CONTENT_NEWS_INDEX->value,
                    'content_admin_news_new',
                    'content_admin_news_show',
                    'content_admin_news_edit',
                    RouteEnum::CONTENT_NEWS_CATEGORY_INDEX->value,
                    'content_admin_news_category_new',
                    'content_admin_news_category_edit',
                ],
                roles: [],
                icon: 'lucide:newspaper',
                group: self::GROUP,
                children: [
                    MenuFactory::item(
                        label: 'Liste des actualités',
                        routes: [RouteEnum::CONTENT_NEWS_INDEX->value, 'content_admin_news_new', 'content_admin_news_show', 'content_admin_news_edit'],
                        roles: [],
                        link: RouteEnum::CONTENT_NEWS_INDEX->value,
                        permission: 'CONTENT_NEWS_VIEW',
                        order: 1,
                    ),
                    MenuFactory::item(
                        label: 'Catégories',
                        routes: [RouteEnum::CONTENT_NEWS_CATEGORY_INDEX->value, 'content_admin_news_category_new', 'content_admin_news_category_edit'],
                        roles: [],
                        link: RouteEnum::CONTENT_NEWS_CATEGORY_INDEX->value,
                        permission: 'CONTENT_NEWS_CATEGORY_VIEW',
                        order: 2,
                    ),
                ],
                groupOrder: 2,
                order: 1,
            ),
            MenuFactory::item(
                label: 'Événements',
                routes: [
                    RouteEnum::CONTENT_EVENT_INDEX->value,
                    'content_admin_event_new',
                    'content_admin_event_show',
                    'content_admin_event_edit',
                    RouteEnum::CONTENT_EVENT_CATEGORY_INDEX->value,
                    'content_admin_event_category_new',
                    'content_admin_event_category_edit',
                ],
                roles: [],
                icon: 'lucide:calendar-days',
                group: self::GROUP,
                children: [
                    MenuFactory::item(
                        label: 'Liste des événements',
                        routes: [RouteEnum::CONTENT_EVENT_INDEX->value, 'content_admin_event_new', 'content_admin_event_show', 'content_admin_event_edit'],
                        roles: [],
                        link: RouteEnum::CONTENT_EVENT_INDEX->value,
                        permission: 'CONTENT_EVENT_VIEW',
                        order: 1,
                    ),
                    MenuFactory::item(
                        label: 'Catégories',
                        routes: [RouteEnum::CONTENT_EVENT_CATEGORY_INDEX->value, 'content_admin_event_category_new', 'content_admin_event_category_edit'],
                        roles: [],
                        link: RouteEnum::CONTENT_EVENT_CATEGORY_INDEX->value,
                        permission: 'CONTENT_EVENT_CATEGORY_VIEW',
                        order: 2,
                    ),
                ],
                groupOrder: 2,
                order: 2,
            ),
            MenuFactory::item(
                label: 'Médias',
                routes: [
                    RouteEnum::CONTENT_VIDEO_INDEX->value,
                    'content_admin_video_new',
                    'content_admin_video_show',
                    'content_admin_video_edit',
                    RouteEnum::CONTENT_GALLERY_INDEX->value,
                    'content_admin_gallery_new',
                    'content_admin_gallery_show',
                    'content_admin_gallery_edit',
                ],
                roles: [],
                icon: 'lucide:images',
                group: self::GROUP,
                children: [
                    MenuFactory::item(
                        label: 'Vidéos',
                        routes: [RouteEnum::CONTENT_VIDEO_INDEX->value, 'content_admin_video_new', 'content_admin_video_show', 'content_admin_video_edit'],
                        roles: [],
                        link: RouteEnum::CONTENT_VIDEO_INDEX->value,
                        permission: 'CONTENT_VIDEO_VIEW',
                        order: 1,
                    ),
                    MenuFactory::item(
                        label: 'Galeries photos',
                        routes: [RouteEnum::CONTENT_GALLERY_INDEX->value, 'content_admin_gallery_new', 'content_admin_gallery_show', 'content_admin_gallery_edit'],
                        roles: [],
                        link: RouteEnum::CONTENT_GALLERY_INDEX->value,
                        permission: 'CONTENT_GALLERY_VIEW',
                        order: 2,
                    ),
                ],
                groupOrder: 2,
                order: 3,
            ),
            MenuFactory::item(
                label: 'Documents',
                routes: [RouteEnum::CONTENT_DOCUMENT_INDEX->value, 'content_admin_document_new', 'content_admin_document_show', 'content_admin_document_edit'],
                roles: [],
                icon: 'lucide:file-text',
                link: RouteEnum::CONTENT_DOCUMENT_INDEX->value,
                group: self::GROUP,
                permission: 'CONTENT_DOCUMENT_VIEW',
                groupOrder: 2,
                order: 4,
            ),
            MenuFactory::item(
                label: 'Pages statiques',
                routes: ['content_admin_page_list', 'content_admin_page_new', 'content_admin_page_show', 'content_admin_page_edit'],
                roles: [],
                icon: 'lucide:layout-template',
                link: 'content_admin_page_list',
                group: self::GROUP,
                permission: 'CONTENT_PAGE_VIEW',
                groupOrder: 2,
                order: 5,
            ),
            MenuFactory::item(
                label: 'Tags',
                routes: [RouteEnum::CONTENT_TAG_INDEX->value, 'content_admin_tag_new', 'content_admin_tag_edit'],
                roles: [],
                icon: 'lucide:tags',
                link: RouteEnum::CONTENT_TAG_INDEX->value,
                group: self::GROUP,
                permission: 'CONTENT_TAG_VIEW',
                groupOrder: 2,
                order: 6,
            ),
        ];
    }
}

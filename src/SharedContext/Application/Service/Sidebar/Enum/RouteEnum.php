<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Sidebar\Enum;

enum RouteEnum: string
{
    case NOTIFICATION_INDEX = 'app_notifications_index';
    case NOTIFICATION_VIEW = 'app_notifications_view';

    case USER_INDEX = 'app_user_index';
    case USER_ADD = 'app_user_add';
    case USER_UPDATE = 'app_user_update';
    case USER_VIEW = 'app_user_view';
    case USER_PROFILE_UPDATE = 'app_user_profile_update';
    case IMAGE_INDEX = 'app_images_index';
    case REGLAGE_INDEX = 'admin_reglages_index';

    case AUTHLOG_INDEX = 'app_auth_log_index';
    case AUTHLOG_VIEW = 'app_auth_log_view';

    case LOG_INDEX = 'app_log_index';
    case MAINTENANCE_INDEX = 'admin_maintenance_edit';
    case CONTENT_NEWS_INDEX = 'content_admin_news_list';
    case CONTENT_NEWS_CATEGORY_INDEX = 'content_admin_news_category_list';
    case CONTENT_TAG_INDEX = 'content_admin_tag_list';
    case CONTENT_EVENT_INDEX = 'content_admin_event_list';
    case CONTENT_EVENT_CATEGORY_INDEX = 'content_admin_event_category_list';
    case CONTENT_VIDEO_INDEX = 'content_admin_video_list';
    case CONTENT_GALLERY_INDEX = 'content_admin_gallery_list';
}

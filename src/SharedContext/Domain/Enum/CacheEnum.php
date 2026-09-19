<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Enum;

enum CacheEnum: string
{
    case CACHE_USER_ALL_OR_SUBORDINATES_LIST = 'cacheWsImmobiliersUserAllOrSubordinatesList';
    case CACHE_NOTIFICATION_LIST = 'cacheWsImmobiliersNotificationList';
    case CACHE_USERS_LIST = 'cacheWsImmobiliersUsersList';
    case CACHE_CURRENCY = 'cacheWsImmobiliersCurrencyList';
    case CACHE_PARAMS = 'cacheWsImmobiliersParams';
    case CACHE_LIST_IMAGE = 'cacheWsImmobiliersListImages';
    case CACHE_DETAIL_IMAGE = 'cacheWsImmobiliersDetailsImages_%s';
    case CACHE_DETAIL_MAINTENANCE = 'cacheWsImmobiliersDetailsMaintenance_%s';
    case CACHE_LIST_PAGINATE_PAGE = 'cacheWsImmobiliersListPaginate';
    case CACHE_LIST_REGLAGE = 'cacheWsImmobiliersListReglage';
    case CACHE_DETAIL_REGLAGE = 'cacheWsImmobiliersDetailsReglage_%s';
    case CACHE_LIST_AUTH_LOG = 'cacheWsImmobiliersAuthLogList';
    case CACHE_LIST_LOG = 'cacheWsImmobiliersLogList';
    case CACHE_LIST_OWNER = 'cacheWsImmobiliersOwnerList';
    case CACHE_LIST_PROPERTY = 'cacheWsImmobiliersPropertyList';
    case CACHE_LIST_AVAILABLE_PROPERTY = 'cacheWsImmobiliersAvailablePropertyList';
    case CACHE_LIST_PROSPECT = 'cacheWsImmobiliersProspectList';
    case CACHE_LIST_CUSTOMER_FILE = 'cacheWsImmobiliersCustomerFileList';
    case CACHE_USERS_LOGIN_LIST = 'cacheWsImmobiliersUserLoginList';
    case CACHE_USERS_LOGOUT_LIST = 'cacheWsImmobiliersUserLogoutList';
    case CACHE_ROLE_PERMISSIONS = 'cacheWsImmobiliersRolePermissions_%s';
    case TAG_ROLE_PERMISSIONS = 'tagWsImmobiliersRolePermissions';

    public function with(int $id): string
    {
        return sprintf($this->value, (string)$id);
    }

    public function withString(string $value): string
    {
        return sprintf($this->value, $value);
    }
}

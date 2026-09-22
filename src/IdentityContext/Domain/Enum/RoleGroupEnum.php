<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Enum;

enum RoleGroupEnum: string
{
    case SUPER = 'SUPER';
    case ADMIN = 'ADMIN';
    case AVOCAT = 'AVOCAT';
    case USER = 'USER';

    // Fonctionnalités historiques. Pour les codes métier configurables,
    // l'autorisation finale est désormais résolue par PermissionEnum.
    case LOGS = 'LOGS';
    case USER_ACCOUNT = 'USER_ACCOUNT';
    case IMAGES = 'IMAGES';
    case REGLAGES = 'REGLAGES';
    case MAINTENANCE = 'MAINTENANCE';
    case NEWS = 'NEWS';
    case CATEGORY_NEWS = 'CATEGORY_NEWS';
    case EVENTS = 'EVENTS';
    case CATEGORY_EVENTS = 'CATEGORY_EVENTS';
    case VIDEOS = 'VIDEOS';
    case CATEGORY_VIDEOS = 'CATEGORY_VIDEOS';
    case GALLERIES = 'GALLERIES';
    case TAGS = 'TAGS';
    case DOCUMENTS = 'DOCUMENTS';
    case PAGES = 'PAGES';
    case TRAININGS = 'TRAININGS';
    case COURSE_MODULES = 'COURSE_MODULES';
    case ENROLLMENTS = 'ENROLLMENTS';
    case CATEGORY_TRAININGS = 'CATEGORY_TRAININGS';
    case TAG_TRAININGS = 'TAG_TRAININGS';
    case PAYMENTS = 'PAYMENTS';
    case PAYMENT_OFFERS = 'PAYMENT_OFFERS';
    case CONTACT_MESSAGES = 'CONTACT_MESSAGES';
    case BATONNIER = 'BATONNIER';

    case ALL = 'ALL';

    /**
     * Résolution finale
     */
    /** @return list<string> */
    public function roles(): array
    {
        $roles = $this->directRoles();

        foreach ($this->children() as $child) {
            $roles = array_merge($roles, $child->roles());
        }

        return array_values(array_unique($roles));
    }

    /**
     * Groupes atomiques → rôles directs
     */
    /** @return list<string> */
    private function directRoles(): array
    {
        return match ($this) {
            self::SUPER => [UserRolesEnum::SUPER_ADMIN->value],
            self::ADMIN => [UserRolesEnum::ADMIN->value],
            self::AVOCAT => [UserRolesEnum::AVOCAT->value],
            self::USER => [UserRolesEnum::USER->value],
            default => [],
        };
    }

    /**
     * Groupes composites → sous-groupes
     */
    /** @return list<self> */
    private function children(): array
    {
        return match ($this) {
            self::LOGS => [self::SUPER],
            self::USER_ACCOUNT => [self::SUPER, self::ADMIN],
            self::IMAGES => [self::SUPER],
            self::REGLAGES => [self::SUPER, self::ADMIN],
            self::MAINTENANCE => [self::SUPER],
            self::NEWS,
            self::CATEGORY_NEWS,
            self::EVENTS,
            self::CATEGORY_EVENTS,
            self::VIDEOS,
            self::CATEGORY_VIDEOS,
            self::GALLERIES,
            self::TAGS,
            self::DOCUMENTS,
            self::PAGES,
            self::TRAININGS,
            self::COURSE_MODULES,
            self::ENROLLMENTS,
            self::CATEGORY_TRAININGS,
            self::TAG_TRAININGS,
            self::PAYMENTS,
            self::PAYMENT_OFFERS,
            self::CONTACT_MESSAGES,
            self::BATONNIER => [self::SUPER, self::ADMIN],

            self::ALL => [
                self::SUPER,
                self::ADMIN,
                self::AVOCAT,
                self::USER,
            ],

            default => [],
        };
    }
}

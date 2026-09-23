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
    case CONTENT_VIDEO_CATEGORY_VIEW = 'CONTENT_VIDEO_CATEGORY_VIEW';
    case CONTENT_VIDEO_CATEGORY_MANAGE = 'CONTENT_VIDEO_CATEGORY_MANAGE';
    case CONTENT_VIDEO_CATEGORY_DELETE = 'CONTENT_VIDEO_CATEGORY_DELETE';
    case CONTENT_GALLERY_VIEW = 'CONTENT_GALLERY_VIEW';
    case CONTENT_GALLERY_MANAGE = 'CONTENT_GALLERY_MANAGE';
    case CONTENT_GALLERY_PUBLISH = 'CONTENT_GALLERY_PUBLISH';
    case CONTENT_GALLERY_DELETE = 'CONTENT_GALLERY_DELETE';
    case CONTENT_DOCUMENT_VIEW = 'CONTENT_DOCUMENT_VIEW';
    case CONTENT_DOCUMENT_MANAGE = 'CONTENT_DOCUMENT_MANAGE';
    case CONTENT_DOCUMENT_PUBLISH = 'CONTENT_DOCUMENT_PUBLISH';
    case CONTENT_DOCUMENT_DELETE = 'CONTENT_DOCUMENT_DELETE';
    case CONTENT_DOCUMENT_RESTRICTED_DOWNLOAD = 'CONTENT_DOCUMENT_RESTRICTED_DOWNLOAD';
    case CONTENT_PAGE_VIEW = 'CONTENT_PAGE_VIEW';
    case CONTENT_PAGE_MANAGE = 'CONTENT_PAGE_MANAGE';
    case CONTENT_PAGE_PUBLISH = 'CONTENT_PAGE_PUBLISH';
    case CONTENT_PAGE_DELETE = 'CONTENT_PAGE_DELETE';
    case BATONNIER_LIST = 'BATONNIER_LIST';
    case BATONNIER_VIEW = 'BATONNIER_VIEW';
    case BATONNIER_CREATE = 'BATONNIER_CREATE';
    case BATONNIER_EDIT = 'BATONNIER_EDIT';
    case COUNCIL_MEMBER_LIST = 'COUNCIL_MEMBER_LIST';
    case COUNCIL_MEMBER_VIEW = 'COUNCIL_MEMBER_VIEW';
    case COUNCIL_MEMBER_CREATE = 'COUNCIL_MEMBER_CREATE';
    case COUNCIL_MEMBER_EDIT = 'COUNCIL_MEMBER_EDIT';
    case CABINET_VIEW = 'CABINET_VIEW';
    case CABINET_CREATE = 'CABINET_CREATE';
    case CABINET_EDIT = 'CABINET_EDIT';
    case LEARNING_TRAINING_VIEW = 'LEARNING_TRAINING_VIEW';
    case LEARNING_TRAINING_MANAGE = 'LEARNING_TRAINING_MANAGE';
    case LEARNING_TRAINING_PUBLISH = 'LEARNING_TRAINING_PUBLISH';
    case LEARNING_TRAINING_DELETE = 'LEARNING_TRAINING_DELETE';
    case LEARNING_ENROLLMENT_VIEW = 'LEARNING_ENROLLMENT_VIEW';
    case LEARNING_ENROLLMENT_MANAGE = 'LEARNING_ENROLLMENT_MANAGE';
    case LEARNING_CATEGORY_VIEW = 'LEARNING_CATEGORY_VIEW';
    case LEARNING_CATEGORY_MANAGE = 'LEARNING_CATEGORY_MANAGE';
    case LEARNING_CATEGORY_DELETE = 'LEARNING_CATEGORY_DELETE';
    case LEARNING_TAG_VIEW = 'LEARNING_TAG_VIEW';
    case LEARNING_TAG_MANAGE = 'LEARNING_TAG_MANAGE';
    case LEARNING_TAG_DELETE = 'LEARNING_TAG_DELETE';
    case PAYMENT_VIEW = 'PAYMENT_VIEW';
    case PAYMENT_OFFER_VIEW = 'PAYMENT_OFFER_VIEW';
    case PAYMENT_OFFER_MANAGE = 'PAYMENT_OFFER_MANAGE';
    case CONTACT_MESSAGE_LIST = 'CONTACT_MESSAGE_LIST';
    case CONTACT_MESSAGE_VIEW = 'CONTACT_MESSAGE_VIEW';
    case CONTACT_MESSAGE_RETRY = 'CONTACT_MESSAGE_RETRY';

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
            self::CONTENT_VIDEO_CATEGORY_VIEW,
            self::CONTENT_VIDEO_CATEGORY_MANAGE,
            self::CONTENT_VIDEO_CATEGORY_DELETE,
            self::CONTENT_GALLERY_VIEW,
            self::CONTENT_GALLERY_MANAGE,
            self::CONTENT_GALLERY_PUBLISH,
            self::CONTENT_GALLERY_DELETE,
            self::CONTENT_DOCUMENT_VIEW,
            self::CONTENT_DOCUMENT_MANAGE,
            self::CONTENT_DOCUMENT_PUBLISH,
            self::CONTENT_DOCUMENT_DELETE,
            self::CONTENT_DOCUMENT_RESTRICTED_DOWNLOAD,
            self::CONTENT_PAGE_VIEW,
            self::CONTENT_PAGE_MANAGE,
            self::CONTENT_PAGE_PUBLISH,
            self::CONTENT_PAGE_DELETE,
            self::BATONNIER_LIST,
            self::BATONNIER_VIEW,
            self::BATONNIER_CREATE,
            self::BATONNIER_EDIT,
            self::COUNCIL_MEMBER_LIST,
            self::COUNCIL_MEMBER_VIEW,
            self::COUNCIL_MEMBER_CREATE,
            self::COUNCIL_MEMBER_EDIT,
            self::CABINET_VIEW,
            self::CABINET_CREATE,
            self::CABINET_EDIT,
            self::LEARNING_TRAINING_VIEW,
            self::LEARNING_TRAINING_MANAGE,
            self::LEARNING_TRAINING_PUBLISH,
            self::LEARNING_TRAINING_DELETE,
            self::LEARNING_ENROLLMENT_VIEW,
            self::LEARNING_ENROLLMENT_MANAGE,
            self::LEARNING_CATEGORY_VIEW,
            self::LEARNING_CATEGORY_MANAGE,
            self::LEARNING_CATEGORY_DELETE,
            self::LEARNING_TAG_VIEW,
            self::LEARNING_TAG_MANAGE,
            self::LEARNING_TAG_DELETE,
            self::PAYMENT_VIEW,
            self::PAYMENT_OFFER_VIEW,
            self::PAYMENT_OFFER_MANAGE,
            self::CONTACT_MESSAGE_LIST,
            self::CONTACT_MESSAGE_VIEW,
            self::CONTACT_MESSAGE_RETRY,
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
            self::CONTENT_VIDEO_CATEGORY_VIEW => 'Catégories de vidéos éditoriales : lecture',
            self::CONTENT_VIDEO_CATEGORY_MANAGE => 'Catégories de vidéos éditoriales : création et édition',
            self::CONTENT_VIDEO_CATEGORY_DELETE => 'Catégories de vidéos éditoriales : suppression',
            self::CONTENT_GALLERY_VIEW => 'Galeries photos : lecture',
            self::CONTENT_GALLERY_MANAGE => 'Galeries photos : création et édition',
            self::CONTENT_GALLERY_PUBLISH => 'Galeries photos : publication et archivage',
            self::CONTENT_GALLERY_DELETE => 'Galeries photos : suppression',
            self::CONTENT_DOCUMENT_VIEW => 'Documents : lecture',
            self::CONTENT_DOCUMENT_MANAGE => 'Documents : création et édition',
            self::CONTENT_DOCUMENT_PUBLISH => 'Documents : publication et archivage',
            self::CONTENT_DOCUMENT_DELETE => 'Documents : suppression',
            self::CONTENT_DOCUMENT_RESTRICTED_DOWNLOAD => 'Documents : téléchargement restreint',
            self::CONTENT_PAGE_VIEW => 'Pages statiques : lecture',
            self::CONTENT_PAGE_MANAGE => 'Pages statiques : création et édition',
            self::CONTENT_PAGE_PUBLISH => 'Pages statiques : publication et dépublication',
            self::CONTENT_PAGE_DELETE => 'Pages statiques : suppression',
            self::BATONNIER_LIST => 'Bâtonnier : liste',
            self::BATONNIER_VIEW => 'Bâtonnier : détail',
            self::BATONNIER_CREATE => 'Bâtonnier : création',
            self::BATONNIER_EDIT => 'Bâtonnier : édition',
            self::COUNCIL_MEMBER_LIST => 'Conseil de l’Ordre : liste',
            self::COUNCIL_MEMBER_VIEW => 'Conseil de l’Ordre : détail',
            self::COUNCIL_MEMBER_CREATE => 'Conseil de l’Ordre : création',
            self::COUNCIL_MEMBER_EDIT => 'Conseil de l’Ordre : édition',
            self::CABINET_VIEW => 'Cabinets : lecture',
            self::CABINET_CREATE => 'Cabinets : création',
            self::CABINET_EDIT => 'Cabinets : édition',
            self::LEARNING_TRAINING_VIEW => 'Formations : lecture',
            self::LEARNING_TRAINING_MANAGE => 'Formations : création et édition',
            self::LEARNING_TRAINING_PUBLISH => 'Formations : publication et archivage',
            self::LEARNING_TRAINING_DELETE => 'Formations : suppression',
            self::LEARNING_ENROLLMENT_VIEW => 'Inscriptions : lecture',
            self::LEARNING_ENROLLMENT_MANAGE => 'Inscriptions : attribution et révocation',
            self::LEARNING_CATEGORY_VIEW => 'Catégories de formations : lecture',
            self::LEARNING_CATEGORY_MANAGE => 'Catégories de formations : création et édition',
            self::LEARNING_CATEGORY_DELETE => 'Catégories de formations : suppression',
            self::LEARNING_TAG_VIEW => 'Tags de formations : lecture',
            self::LEARNING_TAG_MANAGE => 'Tags de formations : création et édition',
            self::LEARNING_TAG_DELETE => 'Tags de formations : suppression',
            self::PAYMENT_VIEW => 'Paiements : lecture',
            self::PAYMENT_OFFER_VIEW => 'Tarifs de formations : lecture',
            self::PAYMENT_OFFER_MANAGE => 'Tarifs de formations : gestion',
            self::CONTACT_MESSAGE_LIST => 'Messages de contact : liste',
            self::CONTACT_MESSAGE_VIEW => 'Messages de contact : détail',
            self::CONTACT_MESSAGE_RETRY => 'Messages de contact : reprise d’envoi',
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
            self::CONTENT_VIDEO_CATEGORY_VIEW => ColorEnum::INFO,
            self::CONTENT_VIDEO_CATEGORY_MANAGE => ColorEnum::SUCCESS,
            self::CONTENT_VIDEO_CATEGORY_DELETE => ColorEnum::DANGER,
            self::CONTENT_GALLERY_VIEW => ColorEnum::INFO,
            self::CONTENT_GALLERY_MANAGE => ColorEnum::SUCCESS,
            self::CONTENT_GALLERY_PUBLISH => ColorEnum::WARNING,
            self::CONTENT_GALLERY_DELETE => ColorEnum::DANGER,
            self::CONTENT_DOCUMENT_VIEW => ColorEnum::INFO,
            self::CONTENT_DOCUMENT_MANAGE => ColorEnum::SUCCESS,
            self::CONTENT_DOCUMENT_PUBLISH => ColorEnum::WARNING,
            self::CONTENT_DOCUMENT_DELETE => ColorEnum::DANGER,
            self::CONTENT_DOCUMENT_RESTRICTED_DOWNLOAD => ColorEnum::WARNING,
            self::CONTENT_PAGE_VIEW => ColorEnum::INFO,
            self::CONTENT_PAGE_MANAGE => ColorEnum::SUCCESS,
            self::CONTENT_PAGE_PUBLISH => ColorEnum::WARNING,
            self::CONTENT_PAGE_DELETE => ColorEnum::DANGER,
            self::BATONNIER_LIST => ColorEnum::INFO,
            self::BATONNIER_VIEW => ColorEnum::INFO,
            self::BATONNIER_CREATE => ColorEnum::SUCCESS,
            self::BATONNIER_EDIT => ColorEnum::WARNING,
            self::COUNCIL_MEMBER_LIST => ColorEnum::INFO,
            self::COUNCIL_MEMBER_VIEW => ColorEnum::INFO,
            self::COUNCIL_MEMBER_CREATE => ColorEnum::SUCCESS,
            self::COUNCIL_MEMBER_EDIT => ColorEnum::WARNING,
            self::LEARNING_TRAINING_VIEW => ColorEnum::INFO,
            self::LEARNING_TRAINING_MANAGE => ColorEnum::SUCCESS,
            self::LEARNING_TRAINING_PUBLISH => ColorEnum::WARNING,
            self::LEARNING_TRAINING_DELETE => ColorEnum::DANGER,
            self::LEARNING_ENROLLMENT_VIEW => ColorEnum::INFO,
            self::LEARNING_ENROLLMENT_MANAGE => ColorEnum::WARNING,
            self::LEARNING_CATEGORY_VIEW => ColorEnum::INFO,
            self::LEARNING_CATEGORY_MANAGE => ColorEnum::SUCCESS,
            self::LEARNING_CATEGORY_DELETE => ColorEnum::DANGER,
            self::LEARNING_TAG_VIEW => ColorEnum::INFO,
            self::LEARNING_TAG_MANAGE => ColorEnum::SUCCESS,
            self::LEARNING_TAG_DELETE => ColorEnum::DANGER,
            self::PAYMENT_VIEW => ColorEnum::INFO,
            self::PAYMENT_OFFER_VIEW => ColorEnum::INFO,
            self::PAYMENT_OFFER_MANAGE => ColorEnum::SUCCESS,
            self::CONTACT_MESSAGE_LIST => ColorEnum::INFO,
            self::CONTACT_MESSAGE_VIEW => ColorEnum::INFO,
            self::CONTACT_MESSAGE_RETRY => ColorEnum::WARNING,
        };
    }
}

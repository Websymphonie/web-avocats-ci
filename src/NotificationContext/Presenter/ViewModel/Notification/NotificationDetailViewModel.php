<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Presenter\ViewModel\Notification;

use Websymphonie\NotificationContext\Domain\Model\Notification\NotificationModel;
use Websymphonie\SharedContext\Domain\ViewModel\DetailViewModel;

/** @extends DetailViewModel<NotificationModel> */
final class NotificationDetailViewModel extends DetailViewModel
{
    public function __construct(NotificationModel $notificationModel)
    {
        parent::__construct($notificationModel);
    }

    public function getNotification(): NotificationModel
    {
        return $this->object;
    }
}
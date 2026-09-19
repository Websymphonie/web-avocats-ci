<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Presenter\ViewModel\Maintenance;

use Websymphonie\AdminContext\Domain\Model\Maintenance\MaintenanceModel;
use Websymphonie\SharedContext\Domain\ViewModel\DetailViewModel;

/** @extends DetailViewModel<MaintenanceModel> */
final class MaintenanceDetailViewModel extends DetailViewModel
{
    public function __construct(MaintenanceModel $model)
    {
        parent::__construct($model);
    }

    public function getObject(): MaintenanceModel
    {
        return $this->object;
    }
}

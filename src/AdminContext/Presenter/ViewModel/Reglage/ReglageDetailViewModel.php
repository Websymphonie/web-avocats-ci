<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Presenter\ViewModel\Reglage;

use Websymphonie\AdminContext\Domain\Model\Reglage\ReglageModel;
use Websymphonie\SharedContext\Domain\ViewModel\DetailViewModel;

/** @extends DetailViewModel<ReglageModel> */
final class ReglageDetailViewModel extends DetailViewModel
{
    public function __construct(ReglageModel $model)
    {
        parent::__construct($model);
    }

    public function getObject(): ReglageModel
    {
        return $this->object;
    }
}

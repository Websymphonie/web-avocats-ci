<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Presenter\ViewModel\Log;

use Websymphonie\LogContext\Domain\Model\Log\LogModel;
use Websymphonie\SharedContext\Domain\ViewModel\DetailViewModel;

/** @extends DetailViewModel<LogModel> */
final class LogDetailViewModel extends DetailViewModel
{
    public function __construct(LogModel $model)
    {
        parent::__construct($model);
    }

    public function getObject(): LogModel
    {
        return $this->object;
    }
}
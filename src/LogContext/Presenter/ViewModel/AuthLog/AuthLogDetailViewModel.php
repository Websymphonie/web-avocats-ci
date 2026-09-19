<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Presenter\ViewModel\AuthLog;

use Websymphonie\LogContext\Domain\Model\AuthLog\AuthLogModel;
use Websymphonie\SharedContext\Domain\ViewModel\DetailViewModel;

/** @extends DetailViewModel<AuthLogModel> */
final class AuthLogDetailViewModel extends DetailViewModel
{
    public function __construct(AuthLogModel $model)
    {
        parent::__construct($model);
    }

    public function getObject(): AuthLogModel
    {
        return $this->object;
    }
}
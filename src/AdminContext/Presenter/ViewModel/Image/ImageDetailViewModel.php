<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Presenter\ViewModel\Image;

use Websymphonie\AdminContext\Domain\Model\Image\ImageModel;
use Websymphonie\SharedContext\Domain\ViewModel\DetailViewModel;

/** @extends DetailViewModel<ImageModel> */
final class ImageDetailViewModel extends DetailViewModel
{
    public function __construct(ImageModel $model)
    {
        parent::__construct($model);
    }

    public function getObject(): ImageModel
    {
        return $this->object;
    }
}

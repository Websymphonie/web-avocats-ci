<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Application\Usecase\Query\Image;

final class ImageDetailsQuery
{
    public function __construct(public string $name)
    {
    }
}

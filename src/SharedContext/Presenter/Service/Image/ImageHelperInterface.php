<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Service\Image;

use Symfony\Component\HttpFoundation\Request;

interface ImageHelperInterface
{
    public function vichImageResolver(
        object  $entity,
        ?string $propertyName = 'filename',
        ?string $fileName = 'imageFile',
        ?bool   $isDoc = false,
        ?bool   $isAvatar = false,
    ): ?string;

    public function getDefaultImagePath(?string $param = 'app.image_default'): string;

    public function imageResolver(string $directory, Request $request, ?string $fileName = null): ?string;
}
<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Service\Image;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

readonly class ImageHelper implements ImageHelperInterface
{

    public function __construct(
        private UploaderHelper     $uploaderHelper,
        private ContainerInterface $container,
    )
    {
    }

    public function vichImageResolver(
        object  $entity,
        ?string $propertyName = 'filename',
        ?string $fileName = 'imageFile',
        ?bool   $isDoc = false,
        ?bool   $isAvatar = false,
    ): ?string
    {
        $fileDefault = $this->getDefaultImagePath();
        if ($isDoc) {
            $fileDefault = null;
        }
        if ($isAvatar) {
            $fileDefault = $this->getDefaultImagePath('app.avatar_default');
        }

        if ($propertyName === null) {
            return $fileDefault;
        }

        return $this->publicPath($this->uploaderHelper->asset($entity, $fileName)) ?? $fileDefault;
    }

    public function getDefaultImagePath(?string $param = 'app.logo_default'): string
    {
        return $this->publicPath((string)$this->container->getParameter($param)) ?? '/assets/logo.svg';
    }

    private function publicPath(?string $asset): ?string
    {
        if ($asset === null || trim($asset) === '') {
            return null;
        }

        $path = parse_url($asset, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return null;
        }

        return '/' . ltrim($path, '/');
    }

    public function imageResolver(
        string  $directory,
        Request $request,
        ?string $fileName = null,
    ): ?string
    {
        $fileDefault = $this->getDefaultImagePath();
        $relativeImagePath = (string)$this->container->getParameter('upload_dir');

        if ($fileName === null) {
            return $fileDefault;
        }

        return $this->publicPath($relativeImagePath . '/' . $directory . '/' . $fileName) ?? $fileDefault;
    }
}

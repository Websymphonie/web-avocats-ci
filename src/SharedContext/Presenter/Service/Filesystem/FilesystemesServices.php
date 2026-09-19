<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Service\Filesystem;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Websymphonie\SharedContext\Domain\Service\Filesystem\FileSystemServiceInterface;

readonly class FilesystemesServices implements FileSystemServiceInterface
{
    public function __construct(
        private Filesystem         $filesystem,
        private ContainerInterface $container
    )
    {
    }

    public function remove(string $file): void
    {
        if ($this->action($file) === true) {
            //On supprime le fichier et on se remet en mode catalogue
            $this->filesystem->remove([$file]);
        }
    }

    private function action(string $filename): bool
    {
        $file = $this->container->getParameter('kernel.project_dir') . '/public/' . $filename;
        if ($this->filesystem->exists([$file])) {
            return true;
        } else {
            return false;
        }
    }

    public function touch(string $file): void
    {

        if ($this->action($file) === false) {
            //On crée le fichier et on se met en mode maintenance
            $this->filesystem->touch($file);
        }
    }
}
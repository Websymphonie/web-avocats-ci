<?php

declare(strict_types=1);

namespace Websymphonie\AdminContext\Infrastructure\Bootstrap;

use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\HttpKernel\KernelInterface;
use Websymphonie\AdminContext\Application\Service\SystemBootstrapData;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;

final readonly class SystemBootstrapper
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private KernelInterface $kernel,
    ) {
    }

    /** @return array{settings: int, images: int} */
    public function bootstrap(): array
    {
        foreach (['logo.png', 'favicon.ico'] as $asset) {
            if (!is_file($this->kernel->getProjectDir() . '/public/assets/' . $asset)) {
                throw new RuntimeException(sprintf('Asset système manquant : public/assets/%s', $asset));
            }
        }

        $createdSettings = 0;
        $createdImages = 0;
        foreach (SystemBootstrapData::settings() as $setting) {
            $existing = $this->entityManager->getRepository(Reglages::class)->findOneBy(['name' => $setting['name']]);
            if ($existing instanceof Reglages) {
                continue;
            }

            $this->entityManager->persist(new Reglages($setting['name'], $setting['label'], $setting['value'], $setting['type']));
            ++$createdSettings;
        }

        foreach (SystemBootstrapData::images() as $image) {
            $existing = $this->entityManager->getRepository(Images::class)->findOneBy(['name' => $image['name']]);
            if ($existing instanceof Images) {
                continue;
            }

            $this->entityManager->persist((new Images())->setName($image['name'])->setLabel($image['label']));
            ++$createdImages;
        }

        $this->entityManager->flush();

        return ['settings' => $createdSettings, 'images' => $createdImages];
    }
}

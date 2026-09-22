<?php

declare(strict_types=1);

namespace Websymphonie\Tests\MediaContext\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\MediaContext\Infrastructure\Service\LocalPublicImageStorage;

final class LocalPublicImageStorageTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/avocat-cnt004-' . bin2hex(random_bytes(5));
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->directory);
    }

    public function testItStoresAnImageUnderThePublicGalleryDirectoryWithARelativeKey(): void
    {
        $source = tempnam(sys_get_temp_dir(), 'cnt004-');
        self::assertNotFalse($source);
        file_put_contents($source, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true));

        $stored = (new LocalPublicImageStorage($this->directory, 5 * 1024 * 1024, new Filesystem()))
            ->store(new UploadedFile($source, 'cover.png', 'image/png', null, true));

        self::assertMatchesRegularExpression('/^galleries\/[a-f0-9]{48}\.png$/', $stored->storagePath);
        self::assertFileExists($this->directory . '/public/' . $stored->storagePath);
    }

    public function testItStoresAContentCoverUnderItsDedicatedPublicPrefix(): void
    {
        $source = tempnam(sys_get_temp_dir(), 'cnt004a-');
        self::assertNotFalse($source);
        file_put_contents($source, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true));

        $stored = (new LocalPublicImageStorage($this->directory, 5 * 1024 * 1024, new Filesystem()))
            ->store(new UploadedFile($source, 'cover.png', 'image/png', null, true), 'content/covers');

        self::assertMatchesRegularExpression('/^content\/covers\/[a-f0-9]{48}\.png$/', $stored->storagePath);
        self::assertFileExists($this->directory . '/public/' . $stored->storagePath);
    }

    public function testItStoresATrainingCoverUnderTheLearningPublicPrefix(): void
    {
        $source = tempnam(sys_get_temp_dir(), 'lrn001-');
        self::assertNotFalse($source);
        file_put_contents($source, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true));

        $stored = (new LocalPublicImageStorage($this->directory, 5 * 1024 * 1024, new Filesystem()))
            ->store(new UploadedFile($source, 'cover.png', 'image/png', null, true), 'training/covers');

        self::assertMatchesRegularExpression('/^training\/covers\/[a-f0-9]{48}\.png$/', $stored->storagePath);
        self::assertFileExists($this->directory . '/public/' . $stored->storagePath);
    }

    public function testItStoresAnInstitutionPortraitUnderItsDedicatedPublicPrefix(): void
    {
        $source = tempnam(sys_get_temp_dir(), 'cnt009-');
        self::assertNotFalse($source);
        file_put_contents($source, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true));

        $stored = (new LocalPublicImageStorage($this->directory, 5 * 1024 * 1024, new Filesystem()))
            ->store(new UploadedFile($source, 'portrait.png', 'image/png', null, true), 'institution/portraits');

        self::assertMatchesRegularExpression('/^institution\/portraits\/[a-f0-9]{48}\.png$/', $stored->storagePath);
        self::assertFileExists($this->directory . '/public/' . $stored->storagePath);
    }
}

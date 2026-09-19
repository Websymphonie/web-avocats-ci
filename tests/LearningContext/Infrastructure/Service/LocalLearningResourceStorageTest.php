<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\LearningContext\Infrastructure\Service\LocalLearningResourceStorage;
use Websymphonie\MediaContext\Domain\Exception\InvalidStoredFileException;
use Websymphonie\MediaContext\Domain\Model\StoredFile;

final class LocalLearningResourceStorageTest extends TestCase
{
    private string $directory;

    protected function setUp(): void { $this->directory = sys_get_temp_dir() . '/avocat-lrn003-' . bin2hex(random_bytes(5)); }
    protected function tearDown(): void { (new Filesystem())->remove($this->directory); }

    public function testItStoresAResourceUnderThePrivateLearningPrefix(): void
    {
        $source = tempnam(sys_get_temp_dir(), 'lrn003-');
        self::assertNotFalse($source);
        file_put_contents($source, "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF\n");
        $storage = new LocalLearningResourceStorage($this->directory, 20 * 1024 * 1024, new Filesystem());
        $stored = $storage->store(new UploadedFile($source, 'support.pdf', 'application/pdf', null, true));

        self::assertMatchesRegularExpression('/^learning\/resources\/[a-f0-9]{48}\.pdf$/', $stored->storageName);
        self::assertFileExists($this->directory . '/private/' . $stored->storageName);
    }

    public function testItRejectsARealMimeAndExtensionMismatch(): void
    {
        $source = tempnam(sys_get_temp_dir(), 'lrn003-');
        self::assertNotFalse($source);
        file_put_contents($source, 'not a document');

        $this->expectException(InvalidStoredFileException::class);
        (new LocalLearningResourceStorage($this->directory, 20 * 1024 * 1024, new Filesystem()))->store(new UploadedFile($source, 'support.pdf', 'application/pdf', null, true));
    }

    public function testLocateRejectsAStorageKeyOutsideTheLearningPrefix(): void
    {
        $storage = new LocalLearningResourceStorage($this->directory, 20 * 1024 * 1024, new Filesystem());

        $this->expectException(\Websymphonie\MediaContext\Domain\Exception\StoredFileNotFoundException::class);
        $storage->locate(new StoredFile(1, 'uuid', 'x.pdf', '../documents/x.pdf', 'application/pdf', 1, 'checksum'));
    }
}

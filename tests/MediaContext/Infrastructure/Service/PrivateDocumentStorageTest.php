<?php

declare(strict_types=1);

namespace Websymphonie\Tests\MediaContext\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\MediaContext\Domain\Exception\InvalidStoredFileException;
use Websymphonie\MediaContext\Infrastructure\Service\PrivateDocumentStorage;

final class PrivateDocumentStorageTest extends TestCase
{
    private string $directory;
    protected function setUp(): void { $this->directory = sys_get_temp_dir() . '/avocat-cnt005-' . bin2hex(random_bytes(5)); }
    protected function tearDown(): void { (new Filesystem())->remove($this->directory); }

    public function testItStoresPdfWithRandomNameAndChecksum(): void
    {
        $source = tempnam(sys_get_temp_dir(), 'cnt005-');
        self::assertNotFalse($source);
        file_put_contents($source, "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF\n");
        $stored = (new PrivateDocumentStorage($this->directory, 20 * 1024 * 1024, new Filesystem()))->store(new UploadedFile($source, 'guide.pdf', 'application/pdf', null, true));
        self::assertSame('application/pdf', $stored->mimeType);
        self::assertMatchesRegularExpression('/^documents\/[a-f0-9]{48}\.pdf$/', $stored->storageName);
        self::assertSame(hash_file('sha256', $this->directory . '/private/' . $stored->storageName), $stored->checksum);
    }

    public function testItRejectsExtensionMimeMismatch(): void
    {
        $source = tempnam(sys_get_temp_dir(), 'cnt005-');
        self::assertNotFalse($source);
        file_put_contents($source, 'not a document');
        $this->expectException(InvalidStoredFileException::class);
        (new PrivateDocumentStorage($this->directory, 20 * 1024 * 1024, new Filesystem()))->store(new UploadedFile($source, 'guide.pdf', 'application/pdf', null, true));
    }
}

<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Service\Filesystem;

interface FileSystemServiceInterface
{
    public function remove(string $file): void;

    public function touch(string $file): void;
}
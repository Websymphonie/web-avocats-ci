<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Application\Usecase\Command\Image;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class UpdateImageCommand
{
    public function __construct(
        public ?int          $id = null,
        public ?string       $name = null,
        public ?string       $label = null,
        public ?string       $filename = null,
        public ?UploadedFile $imageFile = null,
        public ?bool         $deleteFile = false,
    )
    {
    }
}
<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Repository\Images\ImagesRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;

#[Vich\Uploadable]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ImagesRepository::class)]
#[UniqueEntity("name", message: "Le libéllé doit être unique, veuillez réessayer")]
class Images
{
    use IdTrait;
    use DatesTrait;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $label = null;

    #[Vich\UploadableField(mapping: "images", fileNameProperty: "filename")]
    #[Assert\File(
        maxSize: "5M",
        mimeTypes: [
            "image/jpeg",
            "image/png",
            "image/webp",
            "image/svg+xml"
        ],
        mimeTypesMessage: "Format de fichier non supporté (JPEG, PNG, WebP ou SVG uniquement)."
    )]
    private ?File $imageFile = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $filename = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): Images
    {
        $this->label = $label;
        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile): Images
    {
        $this->imageFile = $imageFile;
        if ($this->imageFile instanceof UploadedFile) {
            $this->updatedAt = new DateTimeImmutable('now');
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->label;
    }
}

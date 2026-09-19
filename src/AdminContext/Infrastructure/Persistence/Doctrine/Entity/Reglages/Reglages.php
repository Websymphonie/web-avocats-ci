<?php

declare(strict_types=1);

namespace Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Repository\Reglages\ReglagesRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;


#[ORM\Entity(repositoryClass: ReglagesRepository::class)]
#[UniqueEntity("name", message: "Le champ nom doit être unique, veuillez réessayer")]
#[ORM\HasLifecycleCallbacks]
class Reglages
{
    use IdTrait;
    use DatesTrait;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $label;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $value;

    #[ORM\Column(type: 'text')]
    private string $type;

    public function __construct(string $name, string $label, ?string $value, string $type)
    {
        $this->name = $name;
        $this->label = $label;
        $this->value = $value;
        $this->type = $type;
    }

    public function displayValue(): ?string
    {
        if ($this->getType() === CheckboxType::class) {
            return intval($this->getValue()) === 1 ? 'Oui' : 'Non';
        } else {
            return $this->getValue();
        }
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Reglages
    {
        $this->type = $type;
        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): Reglages
    {
        $this->value = $value;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Reglages
    {
        $this->name = $name;
        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): Reglages
    {
        $this->label = $label;
        return $this;
    }

    public function __toString(): string
    {
        return $this->value ?? '';
    }

}

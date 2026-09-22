<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Application\Service;

use Websymphonie\AdminContext\Domain\Repository\Reglage\ReglageModelRepository;

final readonly class PublicContactSettingsProvider
{
    public function __construct(private ReglageModelRepository $repository)
    {
    }

    public function get(): PublicContactSettings
    {
        return new PublicContactSettings(
            address: $this->value('contact_address'),
            phone: $this->value('contact_phone'),
            email: $this->value('contact_email'),
            hours: $this->value('contact_hours'),
        );
    }

    private function value(string $name): ?string
    {
        $value = $this->repository->getValue($name)?->getValue();
        $value = $value !== null ? trim($value) : null;
        return $value === '' ? null : $value;
    }
}

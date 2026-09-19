<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Component\Commons\Phone;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\SharedContext\Presenter\Service\Intl\WsIntlFormatter;

#[AsTwigComponent('phoneNumber', template: 'shared/components/phone/phone_number_component.html.twig')]
class PhoneNumberComponent
{
    public ?string $number = null;
    public bool $clickable = true;

    public function getFormatted(): ?string
    {
        return WsIntlFormatter::getFormatted($this->number);
    }

    public function getTelLink(): ?string
    {
        return WsIntlFormatter::getTelLink($this->number);
    }
}
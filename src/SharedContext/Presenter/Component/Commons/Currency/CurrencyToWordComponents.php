<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Component\Commons\Currency;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\SharedContext\Domain\Service\Helper\HelpersInterfaces;

#[AsTwigComponent('CurrencyToWord', template: 'shared/components/currency/currency_to_word_component.html.twig')]
class CurrencyToWordComponents
{
    public float $price = 0;
    public ?string $textPrefix = 'Arrêtée la présente facture à la somme de:';
    public ?string $devise = 'XOF';
    public ?bool $isPrefixText = true;
    public ?bool $isCurrency = true;

    public function __construct(private readonly HelpersInterfaces $helpersInterfaces)
    {
    }

    public function getToCurrency(): string
    {
        if ($this->isCurrency) {
            return $this->helpersInterfaces->tocurrency($this->price, $this->devise);
        } else {
            return $this->helpersInterfaces->towords(intval($this->price));
        }
    }
}
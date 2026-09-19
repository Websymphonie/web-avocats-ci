<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<int|null> */
final class AmountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            static fn(?int $amount): string => $amount === null ? '' : number_format($amount, 0, '', ' '),
            static function (mixed $amount): ?int {
                $value = preg_replace('/[\s\x{00A0}\x{202F}]/u', '', trim((string) $amount));
                if ($value === '') {
                    return null;
                }
                if ($value === null || preg_match('/^-?\d+$/', $value) !== 1) {
                    throw new TransformationFailedException('Le montant doit être un nombre entier.');
                }

                return (int) $value;
            },
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'help' => 'Saisissez un montant entier. Les espaces sont acceptés : 25000000 ou 25 000 000.',
            'invalid_message' => 'Saisissez un montant entier, sans symbole monétaire ni décimales.',
        ]);
        $resolver->setNormalizer('attr', static fn(Options $options, array $attr): array => array_replace([
            'class' => 'block h-11 w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm text-foreground shadow-xs outline-none transition-colors placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/30',
            'inputmode' => 'numeric',
            'autocomplete' => 'off',
            'placeholder' => 'Ex. 25 000 000',
        ], $attr));
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}

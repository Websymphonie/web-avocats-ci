<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Presenter\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Websymphonie\PaymentContext\Application\Service\CurrencyCatalogInterface;
use Websymphonie\PaymentContext\Application\Service\TrainingCatalogInterface;
use Websymphonie\PaymentContext\Application\Usecase\Command\SaveTrainingOfferCommand;

/** @extends AbstractType<SaveTrainingOfferCommand> */
final class TrainingOfferFormType extends AbstractType
{
    public function __construct(
        private readonly TrainingCatalogInterface $trainings,
        private readonly CurrencyCatalogInterface $currencies,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $trainingChoices = [];
        foreach ($this->trainings->list(1, 200) as $training) {
            $trainingChoices[$training->title] = $training->id;
        }

        $currencyChoices = [];
        foreach ($this->currencies->listActive() as $currency) {
            $currencyChoices[$currency->label()] = $currency->code;
        }

        $builder
            ->add('trainingId', ChoiceType::class, ['label' => 'Formation', 'choices' => $trainingChoices, 'placeholder' => 'Sélectionner une formation payante', 'disabled' => $options['training_locked']])
            ->add('amount', IntegerType::class, ['label' => 'Montant', 'constraints' => [new GreaterThan(0)], 'attr' => ['min' => 1, 'placeholder' => '10000']])
            ->add('currency', ChoiceType::class, ['label' => 'Devise', 'choices' => $currencyChoices, 'placeholder' => 'Sélectionner une devise'])
            ->add('active', CheckboxType::class, ['label' => 'Tarif actif', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => SaveTrainingOfferCommand::class, 'translation_domain' => false, 'training_locked' => false]);
        $resolver->setAllowedTypes('training_locked', 'bool');
    }
}

<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Presenter\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Websymphonie\PaymentContext\Application\Service\TrainingCatalogInterface;
use Websymphonie\PaymentContext\Application\Usecase\Command\SaveTrainingOfferCommand;

/** @extends AbstractType<SaveTrainingOfferCommand> */
final class TrainingOfferFormType extends AbstractType
{
    public function __construct(private readonly TrainingCatalogInterface $trainings) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [];
        foreach ($this->trainings->list(1, 200) as $training) { $choices[$training->title] = $training->id; }
        $builder
            ->add('trainingId', ChoiceType::class, ['label' => 'Formation', 'choices' => $choices, 'placeholder' => 'Sélectionner une formation payante', 'disabled' => $options['training_locked']])
            ->add('amount', IntegerType::class, ['label' => 'Montant', 'constraints' => [new GreaterThan(0)], 'attr' => ['min' => 1, 'placeholder' => '10000']])
            ->add('currency', TextType::class, ['label' => 'Devise ISO 4217', 'constraints' => [new Length(exactly: 3), new Regex('/^[A-Za-z]{3}$/')], 'attr' => ['maxlength' => 3, 'placeholder' => 'XOF']])
            ->add('active', CheckboxType::class, ['label' => 'Tarif actif', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => SaveTrainingOfferCommand::class, 'translation_domain' => false, 'training_locked' => false]);
        $resolver->setAllowedTypes('training_locked', 'bool');
    }
}

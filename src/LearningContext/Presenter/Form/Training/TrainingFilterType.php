<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Form\Training;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\LearningContext\Application\Usecase\Query\GetTrainingListQuery;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;

/** @extends AbstractType<GetTrainingListQuery> */
final class TrainingFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('search', TextType::class, ['label' => 'Rechercher', 'required' => false, 'attr' => ['placeholder' => 'Rechercher par titre']])
            ->add('status', EnumType::class, ['label' => 'Statut', 'class' => TrainingStatus::class, 'choice_label' => static fn (TrainingStatus $value): string => $value->label(), 'required' => false, 'placeholder' => 'Tous les statuts'])
            ->add('visibility', EnumType::class, ['label' => 'Visibilité', 'class' => TrainingVisibility::class, 'choice_label' => static fn (TrainingVisibility $value): string => $value->label(), 'required' => false, 'placeholder' => 'Toutes les visibilités'])
            ->add('accessType', EnumType::class, ['label' => 'Accès', 'class' => TrainingAccessType::class, 'choice_label' => static fn (TrainingAccessType $value): string => $value->label(), 'required' => false, 'placeholder' => 'Tous les accès']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => GetTrainingListQuery::class, 'method' => 'GET', 'csrf_protection' => false]);
    }

    public function getBlockPrefix(): string { return ''; }
}

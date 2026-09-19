<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Form\Training;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Websymphonie\LearningContext\Application\Usecase\Command\CreateTrainingCommand;
use Websymphonie\LearningContext\Application\Usecase\Command\UpdateTrainingCommand;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;

/** @extends AbstractType<CreateTrainingCommand|UpdateTrainingCommand> */
final class TrainingFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => true,
                'attr' => ['placeholder' => 'Titre de la formation'],
            ])
            ->add('summary', TextareaType::class, [
                'label' => 'Résumé',
                'required' => true,
                'attr' => ['rows' => 3, 'placeholder' => 'Résumé court pour le futur catalogue'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => ['rows' => 12],
            ])
            ->add('visibility', ChoiceType::class, [
                'label' => 'Visibilité',
                'choices' => [
                    'Public' => TrainingVisibility::PUBLIC,
                    'Membres' => TrainingVisibility::MEMBER,
                ],
                'choice_translation_domain' => false,
                'choice_label' => static fn (TrainingVisibility $value): string => $value->label(),
                'choice_attr' => static fn (?TrainingVisibility $value): array => ['title' => $value?->description() ?? ''],
                'required' => true,
            ])
            ->add('accessType', ChoiceType::class, [
                'label' => 'Type d’accès',
                'choices' => [
                    'Gratuit' => TrainingAccessType::FREE,
                    'Payant' => TrainingAccessType::PAID,
                    'Restreint' => TrainingAccessType::RESTRICTED,
                ],
                'choice_translation_domain' => false,
                'choice_label' => static fn (TrainingAccessType $value): string => $value->label(),
                'choice_attr' => static fn (?TrainingAccessType $value): array => ['title' => $value?->description() ?? ''],
                'required' => true,
            ])
            ->add('cover', FileType::class, [
                'label' => 'Image de couverture',
                'required' => false,
                'mapped' => true,
                'attr' => ['accept' => 'image/jpeg,image/png,image/webp'],
                'constraints' => [
                    new File(
                        maxSize: '5M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Seules les images JPEG, PNG et WebP sont acceptées.',
                    ),
                ],
            ]);

        if ($options['data'] instanceof UpdateTrainingCommand) {
            $builder->add('removeCover', CheckboxType::class, [
                'label' => 'Retirer la couverture actuelle',
                'required' => false,
                'mapped' => true,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['translation_domain' => false]);
    }
}

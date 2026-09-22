<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Form\CouncilMember;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Websymphonie\ContentContext\Application\Usecase\Command\CouncilMember\CreateCouncilMemberCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\CouncilMember\UpdateCouncilMemberCommand;

/** @extends AbstractType<CreateCouncilMemberCommand|UpdateCouncilMemberCommand> */
final class CouncilMemberFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'Nom complet',
                'constraints' => [new Length(max: 255)],
                'attr' => ['placeholder' => 'Nom du membre'],
            ])
            ->add('function', TextType::class, [
                'label' => 'Fonction',
                'constraints' => [new Length(max: 255)],
                'attr' => ['placeholder' => 'Fonction au Conseil de l’Ordre'],
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Ordre d’affichage',
                'constraints' => [new PositiveOrZero()],
                'attr' => ['min' => 0],
            ])
            ->add('mandateStartedAt', DateType::class, [
                'label' => 'Début du mandat',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('mandateEndedAt', DateType::class, [
                'label' => 'Fin du mandat',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('portrait', FileType::class, [
                'label' => 'Portrait (facultatif)',
                'required' => false,
                'attr' => ['accept' => 'image/jpeg,image/png,image/webp'],
                'constraints' => [new File(maxSize: '5M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'], mimeTypesMessage: 'Seules les images JPEG, PNG et WebP sont acceptées.')],
            ]);

        if ($options['data'] instanceof UpdateCouncilMemberCommand) {
            $builder->add('removePortrait', CheckboxType::class, [
                'label' => 'Retirer le portrait actuel',
                'required' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['translation_domain' => false]);
    }
}

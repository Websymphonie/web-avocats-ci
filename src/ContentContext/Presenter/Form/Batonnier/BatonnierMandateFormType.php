<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Form\Batonnier;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Websymphonie\ContentContext\Application\Usecase\Command\Batonnier\CreateBatonnierMandateCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\Batonnier\UpdateBatonnierMandateCommand;

/** @extends AbstractType<CreateBatonnierMandateCommand|UpdateBatonnierMandateCommand> */
final class BatonnierMandateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, ['label' => 'Nom complet', 'attr' => ['placeholder' => 'Nom du Bâtonnier']])
            ->add('mandateStartedAt', DateType::class, ['label' => 'Début du mandat', 'widget' => 'single_text', 'input' => 'datetime_immutable'])
            ->add('mandateEndedAt', DateType::class, ['label' => 'Fin du mandat', 'required' => false, 'widget' => 'single_text', 'input' => 'datetime_immutable'])
            ->add('portrait', FileType::class, ['label' => 'Portrait (facultatif)', 'required' => false, 'attr' => ['accept' => 'image/jpeg,image/png,image/webp'], 'constraints' => [new File(maxSize: '5M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'], mimeTypesMessage: 'Seules les images JPEG, PNG et WebP sont acceptées.')]])
            ->add('summary', TextareaType::class, ['label' => 'Présentation courte', 'required' => false, 'attr' => ['rows' => 5], 'constraints' => [new Length(max: 1000)]])
        ;
        if ($options['data'] instanceof UpdateBatonnierMandateCommand) {
            $builder->add('removePortrait', CheckboxType::class, ['label' => 'Retirer le portrait actuel', 'required' => false]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['translation_domain' => false]);
    }
}

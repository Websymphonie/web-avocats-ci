<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Form\CourseModule;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\LearningContext\Application\Usecase\Command\CourseModule\CreateCourseModuleCommand;
use Websymphonie\LearningContext\Application\Usecase\Command\CourseModule\UpdateCourseModuleCommand;

/** @extends AbstractType<CreateCourseModuleCommand|UpdateCourseModuleCommand> */
final class CourseModuleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titre du module', 'required' => true, 'attr' => ['placeholder' => 'Ex. Introduction']])
            ->add('description', TextareaType::class, ['label' => 'Description courte', 'required' => false, 'attr' => ['rows' => 4, 'placeholder' => 'Présentez brièvement ce module.']]);
    }

    public function configureOptions(OptionsResolver $resolver): void { $resolver->setDefaults(['translation_domain' => false]); }
}

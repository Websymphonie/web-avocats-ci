<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Form\CourseModule;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\LearningContext\Application\Usecase\Command\Lesson\CreateLessonCommand;
use Websymphonie\LearningContext\Application\Usecase\Command\Lesson\UpdateLessonCommand;

/** @extends AbstractType<CreateLessonCommand|UpdateLessonCommand> */
final class LessonFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titre de la leçon', 'required' => true, 'attr' => ['placeholder' => 'Ex. Présentation']])
            ->add('summary', TextareaType::class, ['label' => 'Résumé', 'required' => false, 'attr' => ['rows' => 4, 'placeholder' => 'Résumé optionnel de la leçon.']]);
    }

    public function configureOptions(OptionsResolver $resolver): void { $resolver->setDefaults(['translation_domain' => false]); }
}

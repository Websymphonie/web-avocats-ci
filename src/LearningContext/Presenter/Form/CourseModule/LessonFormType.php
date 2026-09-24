<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Form\CourseModule;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Websymphonie\LearningContext\Application\Usecase\Command\Lesson\CreateLessonCommand;
use Websymphonie\LearningContext\Application\Usecase\Command\Lesson\UpdateLessonCommand;
use Websymphonie\LearningContext\Domain\Enum\VideoProvider;

/** @extends AbstractType<CreateLessonCommand|UpdateLessonCommand> */
final class LessonFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titre de la leçon', 'required' => true, 'attr' => ['placeholder' => 'Ex. Présentation']])
            ->add('summary', TextareaType::class, ['label' => 'Résumé', 'required' => false, 'attr' => ['rows' => 4, 'placeholder' => 'Résumé optionnel de la leçon.']])
            ->add('content', TextareaType::class, ['label' => 'Contenu pédagogique', 'required' => false, 'empty_data' => ''])
            ->add('videoProvider', ChoiceType::class, [
                'label' => 'Fournisseur vidéo',
                'choices' => [
                    'YouTube' => VideoProvider::YOUTUBE,
                    'Mux' => VideoProvider::MUX,
                ],
                'choice_value' => static fn (VideoProvider $provider): string => $provider->value,
            ])
            ->add('videoReference', TextType::class, [
                'label' => 'Référence vidéo',
                'required' => false,
                'attr' => ['placeholder' => 'URL YouTube ou Playback ID Mux'],
            ])
            ->add('resourceFiles', FileType::class, [
                'label' => 'Ajouter des ressources',
                'required' => false,
                'multiple' => true,
                'attr' => ['accept' => '.pdf,.docx,.xlsx,.pptx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.openxmlformats-officedocument.presentationml.presentation'],
                'constraints' => [new All([new File(maxSize: '20M', mimeTypes: ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'], mimeTypesMessage: 'Seuls les fichiers PDF, DOCX, XLSX et PPTX sont acceptés.')])],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void { $resolver->setDefaults(['translation_domain' => false]); }
}

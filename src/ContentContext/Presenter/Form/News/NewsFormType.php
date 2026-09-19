<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Form\News;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\ContentContext\Application\Usecase\Command\News\CreateNewsCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\News\UpdateNewsCommand;

/** @extends AbstractType<CreateNewsCommand|UpdateNewsCommand> */
final class NewsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('title', TextType::class, ['label' => 'Titre', 'required' => true, 'attr' => ['placeholder' => 'Titre de l’actualité']])
            ->add('excerpt', TextareaType::class, ['label' => 'Chapeau', 'required' => false, 'attr' => ['rows' => 3, 'placeholder' => 'Résumé court affiché dans les listes']])
            ->add('body', TextareaType::class, ['label' => 'Contenu', 'required' => true, 'attr' => ['rows' => 14, 'placeholder' => 'Contenu de l’actualité']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['translation_domain' => false]);
    }
}

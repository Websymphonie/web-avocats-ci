<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Form\Page;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\ContentContext\Application\Usecase\Command\Page\CreatePageCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\Page\UpdatePageCommand;

/** @extends AbstractType<CreatePageCommand|UpdatePageCommand> */
final class PageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titre', 'attr' => ['placeholder' => 'Titre de la page']])
            ->add('slug', TextType::class, ['label' => 'Slug', 'required' => false, 'help' => 'Laissez vide pour le générer depuis le titre.'])
            ->add('content', TextareaType::class, ['label' => 'Contenu', 'required' => false, 'attr' => ['rows' => 18, 'data-rich-text-editor-target' => 'input']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['translation_domain' => false]);
    }
}

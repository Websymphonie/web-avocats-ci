<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Form\Page;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Websymphonie\ContentContext\Application\Usecase\Command\Page\CreatePageCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\Page\UpdatePageCommand;
use Websymphonie\ContentContext\Domain\Enum\PageGroup;

/** @extends AbstractType<CreatePageCommand|UpdatePageCommand> */
final class PageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titre', 'attr' => ['placeholder' => 'Titre de la page']])
            ->add('slug', TextType::class, ['label' => 'Slug', 'required' => false, 'help' => 'Laissez vide pour le générer depuis le titre.'])
            ->add('content', TextareaType::class, ['label' => 'Contenu', 'required' => false, 'attr' => ['rows' => 18, 'data-rich-text-editor-target' => 'input']])
            ->add('group', EnumType::class, ['label' => 'Groupe', 'class' => PageGroup::class, 'choice_label' => static fn (PageGroup $value): string => $value->label(), 'required' => false, 'placeholder' => 'Aucun groupe'])
            ->add('sortOrder', IntegerType::class, ['label' => 'Ordre d’affichage', 'required' => false, 'empty_data' => 0, 'constraints' => [new PositiveOrZero()], 'help' => 'Les valeurs les plus petites apparaissent en premier dans la navigation du groupe.'])
            ->add('cover', FileType::class, ['label' => 'Image de couverture', 'required' => false, 'mapped' => true, 'attr' => ['accept' => 'image/jpeg,image/png,image/webp'], 'constraints' => [new File(maxSize: '5M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'], mimeTypesMessage: 'Seules les images JPEG, PNG et WebP sont acceptées.')]]);
        if ($options['data'] instanceof UpdatePageCommand) {
            $builder->add('removeCover', CheckboxType::class, ['label' => 'Retirer la couverture actuelle', 'required' => false]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['translation_domain' => false]);
    }
}

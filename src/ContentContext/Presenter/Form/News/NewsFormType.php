<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Form\News;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Websymphonie\ContentContext\Application\Usecase\Command\News\CreateNewsCommand;
use Websymphonie\ContentContext\Domain\Repository\NewsCategoryRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\PhotoGalleryRepositoryInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\News\UpdateNewsCommand;

/** @extends AbstractType<CreateNewsCommand|UpdateNewsCommand> */
final class NewsFormType extends AbstractType
{
    public function __construct(private readonly NewsCategoryRepositoryInterface $categoryRepository, private readonly TagRepositoryInterface $tagRepository, private readonly PhotoGalleryRepositoryInterface $galleryRepository) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('title', TextType::class, ['label' => 'Titre', 'required' => true, 'attr' => ['placeholder' => 'Titre de l’actualité']])
            ->add('excerpt', TextareaType::class, ['label' => 'Chapeau', 'required' => false, 'attr' => ['rows' => 3, 'placeholder' => 'Résumé court affiché dans les listes']])
            ->add('body', TextareaType::class, ['label' => 'Contenu', 'required' => true, 'attr' => ['rows' => 14, 'placeholder' => 'Contenu de l’actualité']]);
        $categories = $this->categoryRepository->list(null, 1, 200)->items;
        $tags = $this->tagRepository->list(null, 1, 200)->items;
        $galleries = $this->galleryRepository->list(null, null, null, 1, 200)->items;
        $builder->add('categories', ChoiceType::class, [
            'label' => 'Catégories',
            'required' => false,
            'multiple' => true,
            'choices' => self::choices($categories),
            'placeholder' => 'Sélectionner une ou plusieurs catégories',
            'attr' => ['data-controller' => 'select-combobox'],
            ])
            ->add('tags', ChoiceType::class, [
                'label' => 'Tags',
                'required' => false,
                'multiple' => true,
                'choices' => self::choices($tags),
                'placeholder' => 'Sélectionner un ou plusieurs tags',
                'attr' => ['data-controller' => 'select-combobox'],
            ])
            ->add('cover', FileType::class, ['label' => 'Image de couverture', 'required' => false, 'mapped' => true, 'attr' => ['accept' => 'image/jpeg,image/png,image/webp'], 'constraints' => [new File(maxSize: '5M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'], mimeTypesMessage: 'Seules les images JPEG, PNG et WebP sont acceptées.')]])
            ->add('photoGalleryId', ChoiceType::class, ['label' => 'Galerie photo', 'required' => false, 'placeholder' => 'Aucune galerie', 'choices' => self::galleryChoices($galleries), 'attr' => ['data-controller' => 'select-combobox']]);
        if ($options['data'] instanceof UpdateNewsCommand) {
            $builder->add('removeCover', CheckboxType::class, ['label' => 'Retirer la couverture actuelle', 'required' => false]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['translation_domain' => false]);
    }

    /**
     * @param list<object> $items
     * @return array<string, int>
     */
    private static function choices(array $items): array
    {
        $choices = [];
        foreach ($items as $item) { $choices[$item->name . ' · ' . $item->slug] = $item->id; }
        return $choices;
    }

    /** @return array{plugins: array{remove_button: array{title: string}}, create: bool, copyClassesToDropdown: bool} */
    private static function multiSelectOptions(): array
    {
        return [
            'plugins' => ['remove_button' => ['title' => 'Retirer cette sélection']],
            'create' => false,
            'copyClassesToDropdown' => true,
        ];
    }

    /**
     * @param list<object> $galleries
     * @return array<string, int>
     */
    private static function galleryChoices(array $galleries): array
    {
        $choices = [];
        foreach ($galleries as $gallery) { $choices[$gallery->title . ' · ' . count($gallery->items) . ' photo(s) · ' . $gallery->status->value] = $gallery->id; }
        return $choices;
    }
}

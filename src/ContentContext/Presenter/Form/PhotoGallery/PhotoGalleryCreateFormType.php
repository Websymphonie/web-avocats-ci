<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Form\PhotoGallery;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery\CreatePhotoGalleryCommand;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;

/** @extends AbstractType<CreatePhotoGalleryCommand> */
final class PhotoGalleryCreateFormType extends AbstractType
{
    public function __construct(private readonly TagRepositoryInterface $tagRepository) {}
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('title', TextType::class, ['label' => 'Titre', 'attr' => ['placeholder' => 'Titre de la galerie']])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false, 'attr' => ['rows' => 12]])
            ->add('tags', ChoiceType::class, ['label' => 'Tags', 'required' => false, 'multiple' => true, 'choices' => self::choices($this->tagRepository->list(null, 1, 200)->items), 'autocomplete' => true, 'tom_select_options' => ['plugins' => ['remove_button' => ['title' => 'Retirer cette sélection']], 'create' => false, 'copyClassesToDropdown' => true]])
            ->add('images', FileType::class, ['label' => 'Images', 'multiple' => true, 'required' => false, 'attr' => ['accept' => 'image/jpeg,image/png,image/webp', 'data-gallery-upload-target' => 'input'], 'constraints' => [new All([new File(maxSize: '5M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'], mimeTypesMessage: 'Seules les images JPEG, PNG et WebP sont acceptées.')])]]);
    }
    public function configureOptions(OptionsResolver $resolver): void { $resolver->setDefaults(['data_class' => CreatePhotoGalleryCommand::class, 'translation_domain' => false]); }
    /**
     * @param list<object{ name: string, slug: string, id: int }> $items
     * @return array<string, int>
     */
    private static function choices(array $items): array { $choices = []; foreach ($items as $item) { $choices[$item->name . ' · ' . $item->slug] = $item->id; } return $choices; }
}

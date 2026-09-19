<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Form\PhotoGallery;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\ContentContext\Application\Usecase\Query\PhotoGallery\GetPhotoGalleryListQuery;
use Websymphonie\ContentContext\Domain\Enum\PhotoGalleryStatus;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;

/** @extends AbstractType<GetPhotoGalleryListQuery> */
final class PhotoGalleryFilterType extends AbstractType
{
    public function __construct(private readonly TagRepositoryInterface $tagRepository) {}
    public function buildForm(FormBuilderInterface $builder, array $options): void { $builder->add('search', TextType::class, ['label' => 'Rechercher', 'required' => false, 'attr' => ['placeholder' => 'Rechercher par titre']])->add('status', EnumType::class, ['label' => 'Statut', 'class' => PhotoGalleryStatus::class, 'choice_label' => static fn (PhotoGalleryStatus $value): string => $value->value, 'required' => false, 'placeholder' => 'Tous les statuts'])->add('tagId', ChoiceType::class, ['label' => 'Tag', 'required' => false, 'choices' => self::choices($this->tagRepository->list(null, 1, 200)->items), 'placeholder' => 'Tous les tags']); }
    public function configureOptions(OptionsResolver $resolver): void { $resolver->setDefaults(['data_class' => GetPhotoGalleryListQuery::class, 'method' => 'GET', 'csrf_protection' => false]); }
    public function getBlockPrefix(): string { return ''; }
    /**
     * @param list<object{ name: string, slug: string, id: int }> $items
     * @return array<string, int>
     */
    private static function choices(array $items): array { $choices = []; foreach ($items as $item) { $choices[$item->name] = $item->id; } return $choices; }
}

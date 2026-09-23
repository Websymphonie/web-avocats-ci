<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Form\News;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\ContentContext\Application\Usecase\Query\News\GetNewsListQuery;
use Websymphonie\ContentContext\Domain\Enum\NewsStatus;
use Websymphonie\ContentContext\Domain\Repository\NewsCategoryRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;

/** @extends AbstractType<GetNewsListQuery> */
final class NewsFilterType extends AbstractType
{
    public function __construct(private readonly NewsCategoryRepositoryInterface $categoryRepository, private readonly TagRepositoryInterface $tagRepository) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('search', TextType::class, ['label' => 'Rechercher', 'required' => false, 'attr' => ['placeholder' => 'Rechercher par titre']])
            ->add('status', EnumType::class, ['label' => 'Statut', 'class' => NewsStatus::class, 'choice_label' => static fn (NewsStatus $status): string => $status->label(), 'required' => false, 'placeholder' => 'Tous les statuts'])
            ->add('categoryId', ChoiceType::class, ['label' => 'Catégorie', 'required' => false, 'choices' => self::choices($this->categoryRepository->list(null, 1, 200)->items), 'placeholder' => 'Toutes les catégories', 'attr' => ['data-controller' => 'select-combobox']])
            ->add('tagId', ChoiceType::class, ['label' => 'Tag', 'required' => false, 'choices' => self::choices($this->tagRepository->list(null, 1, 200)->items), 'placeholder' => 'Tous les tags', 'attr' => ['data-controller' => 'select-combobox']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => GetNewsListQuery::class, 'method' => 'GET', 'csrf_protection' => false]);
    }

    public function getBlockPrefix(): string { return ''; }

    /**
     * @param list<object> $items
     * @return array<string, int>
     */
    private static function choices(array $items): array { $choices = []; foreach ($items as $item) { $choices[$item->name . ' · ' . $item->slug] = $item->id; } return $choices; }
}

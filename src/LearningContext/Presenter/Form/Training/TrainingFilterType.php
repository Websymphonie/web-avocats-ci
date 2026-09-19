<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Form\Training;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\LearningContext\Domain\Repository\TrainingCategoryRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingTagRepositoryInterface;
use Websymphonie\LearningContext\Application\Usecase\Query\GetTrainingListQuery;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;

/** @extends AbstractType<GetTrainingListQuery> */
final class TrainingFilterType extends AbstractType
{
    public function __construct(private readonly TrainingCategoryRepositoryInterface $categories, private readonly TrainingTagRepositoryInterface $tags) {}
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('search', TextType::class, ['label' => 'Rechercher', 'required' => false, 'attr' => ['placeholder' => 'Rechercher par titre']])
            ->add('status', EnumType::class, ['label' => 'Statut', 'class' => TrainingStatus::class, 'choice_label' => static fn (TrainingStatus $value): string => $value->label(), 'required' => false, 'placeholder' => 'Tous les statuts'])
            ->add('visibility', EnumType::class, ['label' => 'Visibilité', 'class' => TrainingVisibility::class, 'choice_label' => static fn (TrainingVisibility $value): string => $value->label(), 'required' => false, 'placeholder' => 'Toutes les visibilités'])
            ->add('accessType', EnumType::class, ['label' => 'Accès', 'class' => TrainingAccessType::class, 'choice_label' => static fn (TrainingAccessType $value): string => $value->label(), 'required' => false, 'placeholder' => 'Tous les accès'])
            ->add('type', EnumType::class, ['label' => 'Type', 'class' => TrainingType::class, 'choice_label' => static fn (TrainingType $value): string => $value->label(), 'required' => false, 'placeholder' => 'Tous les types']);
        $builder->add('categoryId', ChoiceType::class, ['label' => 'Catégorie', 'required' => false, 'placeholder' => 'Toutes les catégories', 'choices' => self::choices($this->categories->list(null, 1, 200)->items), 'autocomplete' => true, 'tom_select_options' => ['create' => false, 'copyClassesToDropdown' => true]])
            ->add('tagId', ChoiceType::class, ['label' => 'Tag', 'required' => false, 'placeholder' => 'Tous les tags', 'choices' => self::tagChoices($this->tags->list(null, 1, 200)->items), 'autocomplete' => true, 'tom_select_options' => ['create' => false, 'copyClassesToDropdown' => true]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => GetTrainingListQuery::class, 'method' => 'GET', 'csrf_protection' => false]);
    }

    public function getBlockPrefix(): string { return ''; }
    /**
     * @param list<object> $items
     * @return array<string, int>
     */
    private static function choices(array $items): array { $choices = []; foreach ($items as $item) { $choices[$item->name] = $item->id; } return $choices; }
    /**
     * @param list<object> $items
     * @return array<string, int>
     */
    private static function tagChoices(array $items): array { return self::choices($items); }
}

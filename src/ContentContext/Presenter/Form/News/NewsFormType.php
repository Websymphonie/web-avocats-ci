<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Form\News;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\ContentContext\Application\Usecase\Command\News\CreateNewsCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\News\UpdateNewsCommand;
use Websymphonie\ContentContext\Domain\Repository\NewsCategoryRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;

/** @extends AbstractType<CreateNewsCommand|UpdateNewsCommand> */
final class NewsFormType extends AbstractType
{
    public function __construct(private readonly NewsCategoryRepositoryInterface $categoryRepository, private readonly TagRepositoryInterface $tagRepository) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('title', TextType::class, ['label' => 'Titre', 'required' => true, 'attr' => ['placeholder' => 'Titre de l’actualité']])
            ->add('excerpt', TextareaType::class, ['label' => 'Chapeau', 'required' => false, 'attr' => ['rows' => 3, 'placeholder' => 'Résumé court affiché dans les listes']])
            ->add('body', TextareaType::class, ['label' => 'Contenu', 'required' => true, 'attr' => ['rows' => 14, 'placeholder' => 'Contenu de l’actualité']]);
        $categories = $this->categoryRepository->list(null, 1, 200)->items;
        $tags = $this->tagRepository->list(null, 1, 200)->items;
        $builder->add('categories', ChoiceType::class, ['label' => 'Catégories', 'required' => false, 'multiple' => true, 'choices' => self::choices($categories), 'placeholder' => 'Sélectionner une ou plusieurs catégories'])
            ->add('tags', ChoiceType::class, ['label' => 'Tags', 'required' => false, 'multiple' => true, 'choices' => self::choices($tags), 'placeholder' => 'Sélectionner un ou plusieurs tags']);
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
}

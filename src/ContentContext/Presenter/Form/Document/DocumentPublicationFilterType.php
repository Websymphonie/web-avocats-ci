<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Form\Document;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\ContentContext\Application\Usecase\Query\Document\GetDocumentPublicationListQuery;
use Websymphonie\ContentContext\Domain\Enum\DocumentAccessLevel;
use Websymphonie\ContentContext\Domain\Enum\DocumentStatus;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;

/** @extends AbstractType<GetDocumentPublicationListQuery> */
final class DocumentPublicationFilterType extends AbstractType
{
    public function __construct(private readonly TagRepositoryInterface $tagRepository) {}
    public function buildForm(FormBuilderInterface $builder, array $options): void { $builder->add('search', TextType::class, ['label' => 'Rechercher', 'required' => false, 'attr' => ['placeholder' => 'Rechercher par titre']])->add('status', EnumType::class, ['label' => 'Statut', 'class' => DocumentStatus::class, 'choice_label' => static fn (DocumentStatus $value): string => $value->value, 'required' => false, 'placeholder' => 'Tous les statuts'])->add('accessLevel', EnumType::class, ['label' => 'Accès', 'class' => DocumentAccessLevel::class, 'choice_label' => static fn (DocumentAccessLevel $value): string => $value->label(), 'required' => false, 'placeholder' => 'Tous les accès'])->add('tagId', ChoiceType::class, ['label' => 'Tag', 'required' => false, 'choices' => self::choices($this->tagRepository->list(null, 1, 200)->items), 'placeholder' => 'Tous les tags']); }
    public function configureOptions(OptionsResolver $resolver): void { $resolver->setDefaults(['data_class' => GetDocumentPublicationListQuery::class, 'method' => 'GET', 'csrf_protection' => false]); }
    public function getBlockPrefix(): string { return ''; }
    /**
     * @param list<object{ name: string, slug: string, id: int }> $items
     * @return array<string, int>
     */
    private static function choices(array $items): array { $choices = []; foreach ($items as $item) { $choices[$item->name] = $item->id; } return $choices; }
}

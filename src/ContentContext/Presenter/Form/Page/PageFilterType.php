<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Form\Page;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\ContentContext\Application\Usecase\Query\Page\GetPageListQuery;
use Websymphonie\ContentContext\Domain\Enum\PageStatus;

/** @extends AbstractType<GetPageListQuery> */
final class PageFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('search', TextType::class, ['label' => 'Rechercher', 'required' => false, 'attr' => ['placeholder' => 'Titre ou slug']])
            ->add('status', EnumType::class, ['label' => 'Statut', 'class' => PageStatus::class, 'choice_label' => static fn (PageStatus $value): string => $value->label(), 'required' => false, 'placeholder' => 'Tous les statuts']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => GetPageListQuery::class, 'method' => 'GET', 'csrf_protection' => false]);
    }
}

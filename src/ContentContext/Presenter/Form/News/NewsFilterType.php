<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Form\News;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\ContentContext\Application\Usecase\Query\News\GetNewsListQuery;
use Websymphonie\ContentContext\Domain\Enum\NewsStatus;

/** @extends AbstractType<GetNewsListQuery> */
final class NewsFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('search', TextType::class, ['label' => 'Rechercher', 'required' => false, 'attr' => ['placeholder' => 'Rechercher par titre']])
            ->add('status', EnumType::class, ['label' => 'Statut', 'class' => NewsStatus::class, 'choice_label' => static fn (NewsStatus $status): string => $status->label(), 'required' => false, 'placeholder' => 'Tous les statuts']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => GetNewsListQuery::class, 'method' => 'GET', 'csrf_protection' => false]);
    }

    public function getBlockPrefix(): string { return ''; }
}

<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Form\Page;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Websymphonie\ContentContext\Application\Model\PagePersonGroupInput;

/** @extends AbstractType<PagePersonGroupInput> */
final class PagePersonGroupFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('key', HiddenType::class)
            ->add('title', TextType::class, ['label' => 'Titre du groupe'])
            ->add('sortOrder', IntegerType::class, ['label' => 'Ordre du groupe', 'required' => false, 'empty_data' => 0, 'constraints' => [new PositiveOrZero()]])
            ->add('entries', CollectionType::class, [
                'entry_type' => PagePersonEntryFormType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__entry__',
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => PagePersonGroupInput::class, 'translation_domain' => false]);
    }
}

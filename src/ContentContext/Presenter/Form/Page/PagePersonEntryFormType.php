<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Form\Page;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Websymphonie\ContentContext\Application\Model\PagePersonEntryInput;

/** @extends AbstractType<PagePersonEntryInput> */
final class PagePersonEntryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('key', HiddenType::class)
            ->add('displayName', TextType::class, ['label' => 'Nom', 'attr' => ['placeholder' => 'Nom de la personne']])
            ->add('roleLabel', TextType::class, ['label' => 'Fonction (facultatif)', 'required' => false])
            ->add('periodLabel', TextType::class, ['label' => 'Période (facultatif)', 'required' => false, 'help' => 'Saisissez la période telle que validée, sans la convertir en dates.'])
            ->add('portrait', FileType::class, ['label' => 'Portrait (facultatif)', 'required' => false, 'mapped' => true, 'attr' => ['accept' => 'image/jpeg,image/png,image/webp'], 'constraints' => [new File(maxSize: '5M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'])]])
            ->add('removePortrait', CheckboxType::class, ['label' => 'Retirer le portrait', 'required' => false])
            ->add('linkUrl', TextType::class, ['label' => 'Lien (facultatif)', 'required' => false])
            ->add('sortOrder', IntegerType::class, ['label' => 'Ordre', 'required' => false, 'empty_data' => 0, 'constraints' => [new PositiveOrZero()]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => PagePersonEntryInput::class, 'translation_domain' => false]);
    }
}

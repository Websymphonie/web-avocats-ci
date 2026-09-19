<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Presenter\Form\Reglage;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\AdminContext\Application\Usecase\Query\Reglage\GetReglagePaginateListQuery;

/** @extends AbstractType<GetReglagePaginateListQuery> */
class ReglageFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('value', TextType::class, [
                'required' => false,
                'translation_domain' => false,
                'label' => "Valeur",
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GetReglagePaginateListQuery::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}

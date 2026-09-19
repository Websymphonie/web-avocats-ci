<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Form\NewsCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\ContentContext\Application\Usecase\Query\NewsCategory\GetNewsCategoryListQuery;
/** @extends AbstractType<GetNewsCategoryListQuery> */
final class NewsCategoryFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void { $builder->add('search', TextType::class, ['label' => 'Rechercher', 'required' => false, 'attr' => ['placeholder' => 'Nom ou slug']]); }
    public function configureOptions(OptionsResolver $resolver): void { $resolver->setDefaults(['data_class' => GetNewsCategoryListQuery::class, 'method' => 'GET', 'csrf_protection' => false]); }
    public function getBlockPrefix(): string { return ''; }
}

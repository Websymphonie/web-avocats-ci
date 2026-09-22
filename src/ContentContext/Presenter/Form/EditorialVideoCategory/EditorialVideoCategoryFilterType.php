<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Form\EditorialVideoCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\ContentContext\Application\Usecase\Query\EditorialVideoCategory\GetEditorialVideoCategoryListQuery;
/** @extends AbstractType<GetEditorialVideoCategoryListQuery> */
final class EditorialVideoCategoryFilterType extends AbstractType { public function buildForm(FormBuilderInterface $builder, array $options): void { $builder->add('search', TextType::class, ['label' => 'Rechercher', 'required' => false, 'attr' => ['placeholder' => 'Rechercher par nom']]); } public function configureOptions(OptionsResolver $resolver): void { $resolver->setDefaults(['data_class' => GetEditorialVideoCategoryListQuery::class, 'method' => 'GET', 'csrf_protection' => false]); } public function getBlockPrefix(): string { return ''; } }

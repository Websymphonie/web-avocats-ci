<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Form\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\ContentContext\Application\Usecase\Command\Tag\CreateTagCommand;
/** @extends AbstractType<CreateTagCommand> */
final class TagFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void { $builder->add('name', TextType::class, ['label' => 'Nom', 'required' => true]); }
    public function configureOptions(OptionsResolver $resolver): void { $resolver->setDefaults(['translation_domain' => false]); }
}

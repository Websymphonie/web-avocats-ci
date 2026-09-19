<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Form\EventCategory;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\ContentContext\Application\Usecase\Command\EventCategory\CreateEventCategoryCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\EventCategory\UpdateEventCategoryCommand;

/** @extends AbstractType<CreateEventCategoryCommand|UpdateEventCategoryCommand> */
final class EventCategoryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void { $builder->add('name', TextType::class, ['label' => 'Nom', 'required' => true])->add('description', TextareaType::class, ['label' => 'Description', 'required' => false, 'attr' => ['rows' => 4]]); }
    public function configureOptions(OptionsResolver $resolver): void { $resolver->setDefaults(['translation_domain' => false]); }
}

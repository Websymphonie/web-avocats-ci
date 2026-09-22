<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Presenter\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\ContactContext\Application\Usecase\Query\Message\GetContactMessageListQuery;
use Websymphonie\ContactContext\Domain\Enum\ContactMessageDeliveryStatus;

/** @extends AbstractType<GetContactMessageListQuery> */
final class ContactMessageFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('status', EnumType::class, [
            'label' => 'Statut email',
            'class' => ContactMessageDeliveryStatus::class,
            'choice_label' => static fn (ContactMessageDeliveryStatus $status): string => $status->label(),
            'required' => false,
            'placeholder' => 'Tous les statuts',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GetContactMessageListQuery::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}

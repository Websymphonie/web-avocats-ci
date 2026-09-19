<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Presenter\Form\Notification;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Presenter\Tiwg\Extension\RolesExtension;
use Websymphonie\NotificationContext\Application\Usecase\Query\Notification\GetNotificationListQuery;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationAccessEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationActionEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationTypeEnum;

/** @extends AbstractType<GetNotificationListQuery> */
class NotificationFilterType extends AbstractType
{
    public function __construct(private readonly RolesExtension $extension)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $options['user'];
        $builder
            ->add('message', TextType::class, [
                'required' => false,
                'translation_domain' => false,
                'label' => "Message",
                'attr' => ['placeholder' => "Saisissez un text pour le message",],
            ])
            ->add('action', EnumType::class, [
                'label' => 'Action',
                'class' => NotificationActionEnum::class,
                'choices' => NotificationActionEnum::cases(),
                'choice_label' => fn(NotificationActionEnum $choice) => $choice->label(),
                'required' => true,
                'placeholder' => 'Action menée',
                'translation_domain' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);
        if ($this->extension->isRole($user, UserRolesEnum::SUPER_ADMIN)) {
            $builder
                ->add('access', EnumType::class, [
                    'label' => 'Canal',
                    'class' => NotificationAccessEnum::class,
                    'choices' => NotificationAccessEnum::cases(),
                    'choice_label' => fn(NotificationAccessEnum $choice) => $choice->label(),
                    'required' => true,
                    'placeholder' => "Niveau d'accès",
                    'translation_domain' => false,
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ]);
        }
        $builder
            ->add('type', EnumType::class, [
                'label' => 'Type',
                'class' => NotificationTypeEnum::class,
                'choices' => NotificationTypeEnum::cases(),
                'choice_label' => fn(NotificationTypeEnum $choice) => $choice->label(),
                'required' => true,
                'placeholder' => "Type de notification",
                'translation_domain' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GetNotificationListQuery::class,
            'method' => 'GET',
            'csrf_protection' => false,
            'user' => null,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}

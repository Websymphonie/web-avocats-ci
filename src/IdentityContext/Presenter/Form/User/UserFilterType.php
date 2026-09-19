<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\IdentityContext\Application\Usecase\Query\User\GetUserListQuery;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;

/** @extends AbstractType<GetUserListQuery> */
class UserFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
                'translation_domain' => false,
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Rechercher un nom'],
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'translation_domain' => false,
                'label' => "Adresse email",
                'attr' => [
                    'placeholder' => "Adresse E-mail",
                ],
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Role',
                'required' => false,
                'translation_domain' => false,
                'choices' => array_combine(
                    array_map(fn($r) => UserRolesEnum::from($r->value)->label(), UserRolesEnum::allRoles()),
                    UserRolesEnum::allRoles()
                ),
            ])
            ->add('enabled', ChoiceType::class, [
                'label' => 'Utilisateur(s) actif(s) ?',
                'required' => true,
                'expanded' => true,
                'multiple' => false,
                'translation_domain' => false,
                'choices' => [
                    'Tous' => null,
                    'Actif(s)' => true,
                    'Non actif(s)' => false,
                ],
                'attr' => ['class' => 'd-flex justify-content-start gap-2'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GetUserListQuery::class,
            'method' => 'GET',
            'csrf_token_id' => 'user_filter',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}

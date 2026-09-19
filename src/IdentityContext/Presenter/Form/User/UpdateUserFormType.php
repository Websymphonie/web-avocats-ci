<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\UpdateUserCommand;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;

/** @extends AbstractType<UpdateUserCommand> */
class UpdateUserFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'translation_domain' => false,
                'label' => 'Nom complet',
                'attr' => ['placeholder' => 'Prénom et nom'],
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'translation_domain' => false,
                'label' => "Adresse email",
                'attr' => ['placeholder' => "Adresse email de l'utilisateur"],
            ])
            ->add('roles', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'translation_domain' => false,
                'choices' => array_map(static fn(UserRolesEnum $role): string => $role->value, UserRolesEnum::roles()),
                'choice_label' => static fn(string $role): string => UserRolesEnum::from($role)->label(),
                'choice_value' => static fn(?string $role): ?string => $role,
                'label' => "Rôle de l'utilisateur",
                'required' => true,
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'Activer le compte ?',
                'required' => false,
                'translation_domain' => false,
                'row_attr' => ['class' => 'form-check form-switch'],
                'attr' => ['class' => 'form-check-input'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UpdateUserCommand::class,
        ]);
    }
}

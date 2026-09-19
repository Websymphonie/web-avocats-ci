<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Form\Password;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\IdentityContext\Application\Usecase\Command\Password\ChangeUserPasswordCommand;

/** @extends AbstractType<ChangeUserPasswordCommand> */
class UsersChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', PasswordType::class, [
                'label' => "Nouveau mot de passe",
                'required' => true,
                'toggle' => true,
                'translation_domain' => false,
                'visible_label' => "Afficher",
                'hidden_label' => "Masquer",
                'attr' => ['placeholder' => 'Votre nouveau mot de passe', 'minlength' => 12],
            ])->add('confirmPassword', PasswordType::class, [
                'label' => "Confirmer le mot de passe",
                'required' => true,
                'toggle' => true,
                'translation_domain' => false,
                'visible_label' => "Afficher",
                'hidden_label' => "Masquer",
                'attr' => ['placeholder' => 'Confirmer votre nouveau mot de passe', 'minlength' => 12],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChangeUserPasswordCommand::class,
            'csrf_token_id' => 'change_user_password',
        ]);
    }
}

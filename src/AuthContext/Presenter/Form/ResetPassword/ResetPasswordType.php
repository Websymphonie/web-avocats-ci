<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Presenter\Form\ResetPassword;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\AuthContext\Application\Usecase\Command\ResetPassword\ResetPasswordCommand;

/** @extends AbstractType<ResetPasswordCommand> */
class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', PasswordType::class, [
                'required' => true,
                'toggle' => true,
                'translation_domain' => false,
                'visible_label' => 'Afficher',
                'hidden_label' => 'Masquer',
                'invalid_message' => 'Les deux mots de passe doivent être identiques.', 'label' => 'Mot de passe',
                'attr' => ['placeholder' => 'Nouveau mot de passe', 'minlength' => 12],
            ])
            ->add('confirmPassword', PasswordType::class, [
                'required' => true,
                'toggle' => true,
                'translation_domain' => false,
                'visible_label' => 'Afficher',
                'hidden_label' => 'Masquer',
                'invalid_message' => 'Les deux mots de passe doivent être identiques.',
                'label' => 'Confirmer le nouveau mot de passe',
                'attr' => ['placeholder' => 'Confirmer mot de passe', 'minlength' => 12],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ResetPasswordCommand::class,
            'csrf_token_id' => 'reset_password',
        ]);
    }
}

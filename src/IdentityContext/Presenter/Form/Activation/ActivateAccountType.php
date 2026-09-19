<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Form\Activation;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\IdentityContext\Application\Usecase\Command\Activation\ActivateAccountCommand;

/** @extends AbstractType<ActivateAccountCommand> */
final class ActivateAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('password', PasswordType::class, ['label' => 'Nouveau mot de passe', 'required' => true, 'translation_domain' => false, 'attr' => ['minlength' => 12, 'autocomplete' => 'new-password']])
            ->add('confirmPassword', PasswordType::class, ['label' => 'Confirmer le mot de passe', 'required' => true, 'translation_domain' => false, 'attr' => ['minlength' => 12, 'autocomplete' => 'new-password']]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ActivateAccountCommand::class, 'csrf_token_id' => 'activate_account']);
    }
}

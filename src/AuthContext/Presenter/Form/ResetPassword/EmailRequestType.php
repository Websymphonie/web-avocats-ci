<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Presenter\Form\ResetPassword;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\AuthContext\Application\Usecase\Command\ResetPassword\EmailRequestCommand;

/** @extends AbstractType<EmailRequestCommand> */
class EmailRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', EmailType::class, [
            'required' => true,
            'label' => false,
            'translation_domain' => false,
            'attr' => [
                'class' => 'form-control',
                'placeholder' => "Saisissez votre adresse e-mail",
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EmailRequestCommand::class,
            'csrf_token_id' => 'email_request',
        ]);
    }
}

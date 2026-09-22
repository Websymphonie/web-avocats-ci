<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Presenter\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Websymphonie\ContactContext\Application\Usecase\Command\SubmitContactMessageCommand;
use Websymphonie\SharedContext\Presenter\Form\FormExtension\AntispamType;

/** @extends AbstractType<SubmitContactMessageCommand> */
final class ContactMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'Nom complet',
                'empty_data' => '',
                'attr' => ['autocomplete' => 'name', 'placeholder' => 'Votre nom complet'],
                'constraints' => [new NotBlank(message: 'Veuillez renseigner votre nom.'), new Length(max: 180)],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'empty_data' => '',
                'attr' => ['autocomplete' => 'email', 'placeholder' => 'vous@exemple.ci'],
                'constraints' => [new NotBlank(message: 'Veuillez renseigner votre adresse email.'), new Email(message: 'Veuillez renseigner une adresse email valide.'), new Length(max: 180)],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['autocomplete' => 'tel', 'placeholder' => 'Facultatif'],
                'constraints' => [new Length(max: 80)],
            ])
            ->add('subject', TextType::class, [
                'label' => 'Sujet',
                'empty_data' => '',
                'attr' => ['placeholder' => 'Le sujet de votre demande'],
                'constraints' => [new NotBlank(message: 'Veuillez renseigner un sujet.'), new Length(max: 180)],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'empty_data' => '',
                'attr' => ['rows' => 7, 'placeholder' => 'Écrivez votre message…'],
                'constraints' => [new NotBlank(message: 'Veuillez renseigner votre message.'), new Length(max: 10000)],
            ])
            ->add('consent', CheckboxType::class, [
                'label' => 'J’accepte que ces informations soient utilisées pour répondre à ma demande.',
                'mapped' => true,
                'constraints' => [new IsTrue(message: 'Votre consentement est nécessaire pour envoyer le message.')],
            ])
            ->add('antispam', AntispamType::class, ['mapped' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SubmitContactMessageCommand::class,
            'csrf_token_id' => 'contact_form',
            'translation_domain' => false,
        ]);
    }
}

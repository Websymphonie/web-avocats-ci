<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\UpdateProfileCommand;

/** @extends AbstractType<UpdateProfileCommand> */
class ProfileFormType extends AbstractType
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UpdateProfileCommand::class,
        ]);
    }
}

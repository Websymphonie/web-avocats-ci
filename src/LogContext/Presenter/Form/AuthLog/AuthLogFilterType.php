<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Presenter\Form\AuthLog;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\LogContext\Application\Usecase\Query\AuthLog\GetAuthLogListQuery;

/** @extends AbstractType<GetAuthLogListQuery> */
class AuthLogFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('userIp', TextType::class, [
                'required' => false,
                'translation_domain' => false,
                'label' => "Adresse IP",
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('emailEntered', EmailType::class, [
                'required' => false,
                'translation_domain' => false,
                'label' => "Adresse email",
                'attr' => [
                    'class' => 'form-control',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GetAuthLogListQuery::class,
            'method' => 'GET',
            'csrf_token_id' => 'auth_log',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}

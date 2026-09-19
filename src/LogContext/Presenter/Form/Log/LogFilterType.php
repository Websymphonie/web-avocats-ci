<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Presenter\Form\Log;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\LogContext\Application\Usecase\Query\Log\GetLogListQuery;

/** @extends AbstractType<GetLogListQuery> */
class LogFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextType::class, [
                'required' => false,
                'translation_domain' => false,
                'label' => "Message",
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('levelName', EmailType::class, [
                'required' => false,
                'translation_domain' => false,
                'label' => "Niveau",
                'attr' => [
                    'class' => 'form-control',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GetLogListQuery::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}

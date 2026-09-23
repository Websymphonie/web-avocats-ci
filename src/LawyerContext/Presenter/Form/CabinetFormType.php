<?php
declare(strict_types=1);

namespace Websymphonie\LawyerContext\Presenter\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\Cabinet\CabinetEntity;

/** @extends AbstractType<CabinetEntity> */
final class CabinetFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom du cabinet', 'constraints' => [new Length(max: 255)]])
            ->add('registrationNumber', TextType::class, ['label' => 'Numéro d’enregistrement', 'required' => false])
            ->add('address', TextType::class, ['label' => 'Adresse', 'required' => false])
            ->add('city', TextType::class, ['label' => 'Ville', 'required' => false])
            ->add('country', TextType::class, ['label' => 'Pays', 'required' => false])
            ->add('phone', TextType::class, ['label' => 'Téléphone', 'required' => false])
            ->add('email', EmailType::class, ['label' => 'Email', 'required' => false, 'constraints' => [new Email()]])
            ->add('websiteUrl', TextType::class, ['label' => 'Site web', 'required' => false])
            ->add('description', TextareaType::class, ['label' => 'Présentation', 'required' => false, 'attr' => ['rows' => 4]])
            ->add('status', ChoiceType::class, ['label' => 'Statut', 'choices' => ['Actif' => 'ACTIVE', 'Inactif' => 'INACTIVE', 'Archivé' => 'ARCHIVED']])
            ->add('directoryVisible', CheckboxType::class, ['label' => 'Visible dans l’annuaire public', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => CabinetEntity::class, 'translation_domain' => false]);
    }
}

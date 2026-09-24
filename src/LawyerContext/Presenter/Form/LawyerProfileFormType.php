<?php
declare(strict_types=1);

namespace Websymphonie\LawyerContext\Presenter\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\LawyerProfile\LawyerProfileEntity;

/** @extends AbstractType<LawyerProfileEntity> */
final class LawyerProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('displayName', TextType::class, ['label' => 'Nom professionnel public', 'required' => true, 'constraints' => [new NotBlank(message: 'Le nom professionnel est obligatoire.'), new Length(max: 255)]])
            ->add('cabinet', CabinetAutocompleteType::class, ['label' => 'Cabinet', 'required' => false])
            ->add('barNumber', TextType::class, ['label' => 'Numéro du Barreau', 'required' => false])
            ->add('professionalStatus', ChoiceType::class, ['label' => 'Statut professionnel', 'choices' => ['Non vérifié' => 'UNKNOWN', 'En activité' => 'ACTIVE', 'Stagiaire' => 'TRAINEE', 'Honoraire' => 'HONORARY', 'Suspendu' => 'SUSPENDED']])
            ->add('specializationSummary', TextType::class, ['label' => 'Domaines de pratique', 'required' => false, 'attr' => ['placeholder' => 'Ex. Droit des affaires, droit du travail']])
            ->add('professionalEmail', EmailType::class, ['label' => 'Email professionnel', 'required' => false, 'constraints' => [new Email(message: 'Saisissez une adresse email professionnelle valide.'), new Length(max: 255)]])
            ->add('professionalPhone', TextType::class, ['label' => 'Téléphone professionnel', 'required' => false, 'constraints' => [new Length(max: 80), new Regex(pattern: '/^(?=.*\d)[0-9+().\/\s-]{6,80}$/D', message: 'Saisissez un numéro professionnel valide.')]])
            ->add('directoryVisible', CheckboxType::class, ['label' => 'Afficher mon profil dans l’annuaire public', 'required' => false])
            ->add('portrait', FileType::class, ['label' => 'Portrait professionnel (facultatif)', 'mapped' => false, 'required' => false, 'attr' => ['accept' => 'image/jpeg,image/png,image/webp'], 'constraints' => [new File(maxSize: '5M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'], mimeTypesMessage: 'Seules les images JPEG, PNG et WebP sont acceptées.')]])
            ->add('removePortrait', CheckboxType::class, ['label' => 'Retirer le portrait actuel', 'mapped' => false, 'required' => false])
            ->add('bio', TextareaType::class, ['label' => 'Présentation professionnelle', 'required' => false, 'attr' => ['rows' => 5]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => LawyerProfileEntity::class, 'translation_domain' => false]);
    }
}

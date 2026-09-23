<?php
declare(strict_types=1);

namespace Websymphonie\LawyerContext\Presenter\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\Cabinet\CabinetEntity;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\LawyerProfile\LawyerProfileEntity;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Repository\Cabinet\CabinetRepository;

final class LawyerProfileFormType extends AbstractType
{
    public function __construct(private readonly CabinetRepository $cabinetRepository) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cabinet', EntityType::class, [
                'label' => 'Cabinet', 'class' => CabinetEntity::class, 'choice_label' => 'name', 'required' => false,
                'placeholder' => 'Sélectionner un cabinet',
                'query_builder' => fn (CabinetRepository $repo) => $repo->createQueryBuilder('cabinet')->andWhere('cabinet.status = :status')->setParameter('status', 'ACTIVE')->orderBy('cabinet.name', 'ASC'),
            ])
            ->add('barNumber', TextType::class, ['label' => 'Numéro du Barreau', 'required' => false])
            ->add('professionalStatus', ChoiceType::class, ['label' => 'Statut professionnel', 'choices' => ['En activité' => 'ACTIVE', 'Stagiaire' => 'TRAINEE', 'Honoraire' => 'HONORARY', 'Suspendu' => 'SUSPENDED']])
            ->add('specializationSummary', TextType::class, ['label' => 'Domaines de pratique', 'required' => false, 'attr' => ['placeholder' => 'Ex. Droit des affaires, droit du travail']])
            ->add('bio', TextareaType::class, ['label' => 'Présentation professionnelle', 'required' => false, 'attr' => ['rows' => 5]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => LawyerProfileEntity::class, 'translation_domain' => false]);
    }
}

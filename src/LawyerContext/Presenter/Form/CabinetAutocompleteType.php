<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Presenter\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\Cabinet\CabinetEntity;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Repository\Cabinet\CabinetRepository;

/** @extends AbstractType<CabinetEntity> */
#[AsEntityAutocompleteField]
final class CabinetAutocompleteType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => CabinetEntity::class,
            'choice_label' => 'name',
            'placeholder' => 'Rechercher un cabinet',
            'loading_more_text' => 'Recherche des cabinets…',
            'no_results_found_text' => 'Aucun cabinet trouvé.',
            'no_more_results_text' => 'Tous les résultats sont affichés.',
            'query_builder' => static fn (CabinetRepository $repository) => $repository->createQueryBuilder('cabinet')
                ->andWhere('cabinet.status = :status')
                ->setParameter('status', 'ACTIVE')
                ->orderBy('cabinet.name', 'ASC'),
            'searchable_fields' => ['name'],
            'min_characters' => 2,
            'max_results' => 20,
            'security' => 'ROLE_EDIT',
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'cabinet_autocomplete';
    }
}

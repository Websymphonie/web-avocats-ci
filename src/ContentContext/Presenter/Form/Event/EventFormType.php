<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Form\Event;

use DateTimeImmutable;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\File;
use Websymphonie\ContentContext\Application\Usecase\Command\Event\CreateEventCommand;
use Websymphonie\ContentContext\Domain\Enum\EventFormat;
use Websymphonie\ContentContext\Domain\Repository\EventCategoryRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\PhotoGalleryRepositoryInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\Event\UpdateEventCommand;

/** @extends AbstractType<CreateEventCommand|UpdateEventCommand> */
final class EventFormType extends AbstractType
{
    public function __construct(private readonly EventCategoryRepositoryInterface $categoryRepository, private readonly TagRepositoryInterface $tagRepository, private readonly PhotoGalleryRepositoryInterface $galleryRepository) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('title', TextType::class, ['label' => 'Titre', 'required' => true, 'attr' => ['placeholder' => 'Titre de l’événement']])
            ->add('excerpt', TextareaType::class, ['label' => 'Résumé', 'required' => false, 'attr' => ['rows' => 3, 'placeholder' => 'Résumé court affiché dans les listes']])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => true, 'attr' => ['rows' => 14, 'placeholder' => 'Description de l’événement']])
            ->add('format', ChoiceType::class, ['label' => 'Format', 'choices' => self::formatChoices(), 'choice_translation_domain' => false])
            ->add('startsAt', DateTimeType::class, ['label' => 'Début', 'required' => true, 'input' => 'datetime_immutable', 'widget' => 'single_text', 'html5' => true])
            ->add('endsAt', DateTimeType::class, ['label' => 'Fin', 'required' => false, 'input' => 'datetime_immutable', 'widget' => 'single_text', 'html5' => true])
            ->add('venueName', TextType::class, ['label' => 'Nom du lieu', 'required' => false, 'attr' => ['placeholder' => 'Ex. Maison de l’Avocat']])
            ->add('address', TextareaType::class, ['label' => 'Adresse', 'required' => false, 'attr' => ['rows' => 2, 'placeholder' => 'Adresse complète']])
            ->add('onlineUrl', TextType::class, ['label' => 'URL de participation', 'required' => false, 'attr' => ['type' => 'url', 'placeholder' => 'https://…'], 'constraints' => [new Url(protocols: ['http', 'https'], message: 'Utilisez une URL http ou https valide.')]])
            ->add('categories', ChoiceType::class, ['label' => 'Catégories', 'required' => false, 'multiple' => true, 'choices' => self::choices($this->categoryRepository->list(null, 1, 200)->items), 'placeholder' => 'Sélectionner une ou plusieurs catégories', 'attr' => ['data-controller' => 'select-combobox']])
            ->add('tags', ChoiceType::class, ['label' => 'Tags', 'required' => false, 'multiple' => true, 'choices' => self::choices($this->tagRepository->list(null, 1, 200)->items), 'placeholder' => 'Sélectionner un ou plusieurs tags', 'attr' => ['data-controller' => 'select-combobox']])
            ->add('cover', FileType::class, ['label' => 'Image de couverture', 'required' => false, 'mapped' => true, 'attr' => ['accept' => 'image/jpeg,image/png,image/webp'], 'constraints' => [new File(maxSize: '5M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'], mimeTypesMessage: 'Seules les images JPEG, PNG et WebP sont acceptées.')]])
            ->add('photoGalleryId', ChoiceType::class, ['label' => 'Galerie photo', 'required' => false, 'placeholder' => 'Aucune galerie', 'choices' => self::galleryChoices($this->galleryRepository->list(null, null, null, 1, 200)->items), 'attr' => ['data-controller' => 'select-combobox']]);
        if ($options['data'] instanceof UpdateEventCommand) {
            $builder->add('removeCover', CheckboxType::class, ['label' => 'Retirer la couverture actuelle', 'required' => false]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['translation_domain' => false, 'constraints' => [new Callback([self::class, 'validate'])]]);
    }

    public static function validate(mixed $data, ExecutionContextInterface $context): void
    {
        if (!is_object($data)) { return; }
        $format = $data->format ?? null;
        $startsAt = $data->startsAt ?? null;
        $endsAt = $data->endsAt ?? null;
        if ($startsAt instanceof DateTimeImmutable && $endsAt instanceof DateTimeImmutable && $endsAt < $startsAt) { $context->buildViolation('La date de fin doit être postérieure ou égale à la date de début.')->atPath('endsAt')->addViolation(); }
        if (!($format instanceof EventFormat)) { return; }
        $physical = trim((string) ($data->venueName ?? '')) !== '' && trim((string) ($data->address ?? '')) !== '';
        $online = trim((string) ($data->onlineUrl ?? '')) !== '';
        if (in_array($format, [EventFormat::IN_PERSON, EventFormat::HYBRID], true) && !$physical) { $context->buildViolation('Le nom du lieu et l’adresse sont requis pour ce format.')->atPath('venueName')->addViolation(); }
        if (in_array($format, [EventFormat::ONLINE, EventFormat::HYBRID], true) && !$online) { $context->buildViolation('Une URL de participation est requise pour ce format.')->atPath('onlineUrl')->addViolation(); }
    }

    /** @return array<string, EventFormat> */
    private static function formatChoices(): array { return ['Présentiel' => EventFormat::IN_PERSON, 'En ligne' => EventFormat::ONLINE, 'Hybride' => EventFormat::HYBRID]; }
    /**
     * @param list<object> $items
     * @return array<string, int>
     */
    private static function choices(array $items): array { $choices = []; foreach ($items as $item) { $choices[$item->name . ' · ' . $item->slug] = $item->id; } return $choices; }
    /** @return array{plugins: array{remove_button: array{title: string}}, create: bool, copyClassesToDropdown: bool} */
    private static function multiSelectOptions(): array { return ['plugins' => ['remove_button' => ['title' => 'Retirer cette sélection']], 'create' => false, 'copyClassesToDropdown' => true]; }
    /**
     * @param list<object> $galleries
     * @return array<string, int>
     */
    private static function galleryChoices(array $galleries): array { $choices = []; foreach ($galleries as $gallery) { $choices[$gallery->title . ' · ' . count($gallery->items) . ' photo(s) · ' . $gallery->status->value] = $gallery->id; } return $choices; }
}

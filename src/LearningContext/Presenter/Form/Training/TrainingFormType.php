<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Form\Training;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Url;
use Websymphonie\LearningContext\Application\Usecase\Command\CreateTrainingCommand;
use Websymphonie\LearningContext\Application\Usecase\Command\UpdateTrainingCommand;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\LiveDeliveryMode;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;
use Websymphonie\LearningContext\Domain\Repository\TrainingCategoryRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingTagRepositoryInterface;

/** @extends AbstractType<CreateTrainingCommand|UpdateTrainingCommand> */
final class TrainingFormType extends AbstractType
{
    public function __construct(private readonly TrainingCategoryRepositoryInterface $categories, private readonly TrainingTagRepositoryInterface $tags) {}
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => true,
                'attr' => ['placeholder' => 'Titre de la formation'],
            ])
            ->add('summary', TextareaType::class, [
                'label' => 'Résumé',
                'required' => true,
                'attr' => ['rows' => 3, 'placeholder' => 'Résumé court pour le futur catalogue'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => ['rows' => 12],
            ])
            ->add('visibility', ChoiceType::class, [
                'label' => 'Visibilité',
                'choices' => [
                    'Public' => TrainingVisibility::PUBLIC,
                    'Membres' => TrainingVisibility::MEMBER,
                ],
                'choice_translation_domain' => false,
                'choice_label' => static fn (TrainingVisibility $value): string => $value->label(),
                'choice_attr' => static fn (?TrainingVisibility $value): array => ['title' => $value?->description() ?? ''],
                'required' => true,
            ])
            ->add('accessType', ChoiceType::class, [
                'label' => 'Type d’accès',
                'choices' => [
                    'Gratuit' => TrainingAccessType::FREE,
                    'Payant' => TrainingAccessType::PAID,
                    'Restreint' => TrainingAccessType::RESTRICTED,
                ],
                'choice_translation_domain' => false,
                'choice_label' => static fn (TrainingAccessType $value): string => $value->label(),
                'choice_attr' => static fn (?TrainingAccessType $value): array => ['title' => $value?->description() ?? ''],
                'required' => true,
            ])
            ->add('categoryIds', ChoiceType::class, ['label' => 'Catégories', 'required' => false, 'multiple' => true, 'choices' => self::choices($this->categories->list(null, 1, 200)->items), 'attr' => ['data-controller' => 'select-combobox']])
            ->add('tagIds', ChoiceType::class, ['label' => 'Tags', 'required' => false, 'multiple' => true, 'choices' => self::tagChoices($this->tags->list(null, 1, 200)->items), 'attr' => ['data-controller' => 'select-combobox']])
            ->add('cover', FileType::class, [
                'label' => 'Image de couverture',
                'required' => false,
                'mapped' => true,
                'attr' => ['accept' => 'image/jpeg,image/png,image/webp'],
                'constraints' => [
                    new File(
                        maxSize: '5M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Seules les images JPEG, PNG et WebP sont acceptées.',
                    ),
                ],
            ]);

        if (($options['data'] instanceof CreateTrainingCommand || $options['data'] instanceof UpdateTrainingCommand) && $options['data']->type === TrainingType::LIVE) {
            $builder
                ->add('startsAt', DateTimeType::class, ['label' => 'Date/heure de début', 'required' => true, 'input' => 'datetime_immutable', 'widget' => 'single_text', 'html5' => true])
                ->add('endsAt', DateTimeType::class, ['label' => 'Date/heure de fin', 'required' => true, 'input' => 'datetime_immutable', 'widget' => 'single_text', 'html5' => true])
                ->add('deliveryMode', ChoiceType::class, [
                    'label' => 'Mode',
                    'choices' => self::deliveryModeChoices(),
                    'choice_translation_domain' => false,
                    'choice_label' => static fn (LiveDeliveryMode $value): string => $value->label(),
                    'choice_value' => static fn (?LiveDeliveryMode $value): ?string => $value?->value,
                ])
                ->add('location', TextType::class, ['label' => 'Lieu', 'required' => false, 'attr' => ['placeholder' => 'Adresse ou localisation']])
                ->add('joinUrl', TextType::class, ['label' => 'Lien de connexion', 'required' => false, 'attr' => ['type' => 'url', 'placeholder' => 'https://…'], 'constraints' => [new Url(protocols: ['https'], requireTld: false, message: 'Utilisez une URL HTTPS valide.')]])
                ->add('liveVideoReferenceUrl', TextType::class, ['label' => 'Référence du direct (YouTube)', 'required' => false, 'attr' => ['type' => 'url', 'placeholder' => 'https://www.youtube.com/watch?v=…'], 'constraints' => [new Url(protocols: ['https'], requireTld: false, message: 'Utilisez une URL YouTube HTTPS valide.')], 'help' => 'Référence YouTube du direct. Les autres fournisseurs ne sont pas encore disponibles dans le Backoffice.'])
                ->add('replayVideoReferenceUrl', TextType::class, ['label' => 'Référence du replay (YouTube)', 'required' => false, 'attr' => ['type' => 'url', 'placeholder' => 'https://www.youtube.com/watch?v=…'], 'constraints' => [new Url(protocols: ['https'], requireTld: false, message: 'Utilisez une URL YouTube HTTPS valide.')], 'help' => 'Facultatif. Le replay est distinct du direct et ne sera affiché qu’après la fin de la session.']);
        }

        if ($options['data'] instanceof UpdateTrainingCommand) {
            $builder->add('removeCover', CheckboxType::class, [
                'label' => 'Retirer la couverture actuelle',
                'required' => false,
                'mapped' => true,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['translation_domain' => false]);
    }

    /**
     * @param list<object> $items
     * @return array<string, int>
     */
    private static function choices(array $items): array { $choices = []; foreach ($items as $item) { $choices[$item->name] = $item->id; } return $choices; }
    /**
     * @param list<object> $items
     * @return array<string, int>
     */
    private static function tagChoices(array $items): array { return self::choices($items); }

    /** @return array<string, LiveDeliveryMode> */
    private static function deliveryModeChoices(): array
    {
        return ['En ligne' => LiveDeliveryMode::ONLINE, 'Présentiel' => LiveDeliveryMode::IN_PERSON, 'Hybride' => LiveDeliveryMode::HYBRID];
    }
}

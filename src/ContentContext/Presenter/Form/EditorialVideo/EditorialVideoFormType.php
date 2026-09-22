<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Form\EditorialVideo;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo\CreateEditorialVideoCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo\UpdateEditorialVideoCommand;
use Websymphonie\ContentContext\Domain\Enum\VideoProvider;
use Websymphonie\ContentContext\Domain\Model\EditorialVideo;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoCategoryRepositoryInterface;

/** @extends AbstractType<CreateEditorialVideoCommand|UpdateEditorialVideoCommand> */
final class EditorialVideoFormType extends AbstractType
{
    public function __construct(private readonly TagRepositoryInterface $tagRepository, private readonly EditorialVideoCategoryRepositoryInterface $categoryRepository) {}
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('title', TextType::class, ['label' => 'Titre', 'required' => true, 'attr' => ['placeholder' => 'Titre de la vidéo']])
            ->add('excerpt', TextareaType::class, ['label' => 'Résumé', 'required' => false, 'attr' => ['rows' => 3, 'placeholder' => 'Résumé court pour le listing']])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false, 'attr' => ['rows' => 12]])
            ->add('provider', ChoiceType::class, ['label' => 'Fournisseur', 'choices' => ['YouTube' => VideoProvider::YOUTUBE, 'URL externe' => VideoProvider::EXTERNAL_URL], 'choice_translation_domain' => false, 'attr' => ['data-editorial-video-form-target' => 'provider']])
            ->add('videoUrl', TextType::class, ['label' => 'URL de la vidéo', 'required' => true, 'attr' => ['type' => 'url', 'placeholder' => 'https://www.youtube.com/watch?v=…', 'data-editorial-video-form-target' => 'videoUrl'], 'constraints' => [new Url(protocols: ['http', 'https'], message: 'Utilisez une URL http ou https valide.')]])
            ->add('categoryId', ChoiceType::class, ['label' => 'Catégorie', 'required' => true, 'choices' => self::choices($this->categoryRepository->list(null, 1, 200)->items), 'placeholder' => 'Sélectionner une catégorie'])
            ->add('tags', ChoiceType::class, ['label' => 'Tags', 'required' => false, 'multiple' => true, 'choices' => self::choices($this->tagRepository->list(null, 1, 200)->items), 'placeholder' => 'Sélectionner un ou plusieurs tags', 'autocomplete' => true, 'tom_select_options' => ['plugins' => ['remove_button' => ['title' => 'Retirer cette sélection']], 'create' => false, 'copyClassesToDropdown' => true]]);
    }
    public function configureOptions(OptionsResolver $resolver): void { $resolver->setDefaults(['translation_domain' => false, 'constraints' => [new Callback([self::class, 'validate'])]]); }
    public static function validate(mixed $data, ExecutionContextInterface $context): void
    {
        if (!is_object($data) || !isset($data->provider, $data->videoUrl) || !($data->provider instanceof VideoProvider)) { return; }
        if ($data->provider === VideoProvider::YOUTUBE && EditorialVideo::youtubeIdFromUrl($data->provider, (string) $data->videoUrl) === null) { $context->buildViolation('Utilisez une URL YouTube watch, youtu.be ou embed valide.')->atPath('videoUrl')->addViolation(); }
    }
    /**
     * @param list<object{ name: string, slug: string, id: int }> $items
     * @return array<string, int>
     */
    private static function choices(array $items): array { $choices = []; foreach ($items as $item) { $choices[$item->name . ' · ' . $item->slug] = $item->id; } return $choices; }
}

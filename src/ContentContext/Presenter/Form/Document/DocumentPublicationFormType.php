<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Form\Document;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Websymphonie\ContentContext\Application\Usecase\Command\Document\CreateDocumentPublicationCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\Document\UpdateDocumentPublicationCommand;
use Websymphonie\ContentContext\Domain\Enum\DocumentAccessLevel;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;

/** @extends AbstractType<CreateDocumentPublicationCommand|UpdateDocumentPublicationCommand> */
final class DocumentPublicationFormType extends AbstractType
{
    public function __construct(private readonly TagRepositoryInterface $tagRepository) {}
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('title', TextType::class, ['label' => 'Titre', 'attr' => ['placeholder' => 'Titre de la publication']])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false, 'attr' => ['rows' => 12]])
            ->add('accessLevel', EnumType::class, ['label' => 'Niveau d’accès', 'class' => DocumentAccessLevel::class, 'choice_label' => static fn (DocumentAccessLevel $value): string => match ($value) { DocumentAccessLevel::PUBLIC => 'Public', DocumentAccessLevel::MEMBER => 'Membre connecté', DocumentAccessLevel::RESTRICTED => 'Restreint', DocumentAccessLevel::PRIVATE => 'Privé Backoffice' }])
            ->add('tags', ChoiceType::class, ['label' => 'Tags', 'required' => false, 'multiple' => true, 'choices' => self::choices($this->tagRepository->list(null, 1, 200)->items), 'autocomplete' => true, 'tom_select_options' => ['plugins' => ['remove_button' => ['title' => 'Retirer cette sélection']], 'create' => false, 'copyClassesToDropdown' => true]]);
        if ($options['include_file']) { $builder->add('file', FileType::class, ['label' => 'Fichier', 'required' => true, 'attr' => ['accept' => '.pdf,.docx,.xlsx,.pptx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.openxmlformats-officedocument.presentationml.presentation'], 'constraints' => [new File(maxSize: '20M', mimeTypes: ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'], mimeTypesMessage: 'Seuls les fichiers PDF, DOCX, XLSX et PPTX sont acceptés.')]]); }
    }
    public function configureOptions(OptionsResolver $resolver): void { $resolver->setDefaults(['translation_domain' => false, 'include_file' => false]); $resolver->setAllowedTypes('include_file', 'bool'); }
    public function getBlockPrefix(): string { return 'document_publication'; }
    /**
     * @param list<object{ name: string, slug: string, id: int }> $items
     * @return array<string, int>
     */
    private static function choices(array $items): array { $choices = []; foreach ($items as $item) { $choices[$item->name . ' · ' . $item->slug] = $item->id; } return $choices; }
}

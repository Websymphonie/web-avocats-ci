<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Form\PhotoGallery;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery\AddPhotoGalleryImagesCommand;

/** @extends AbstractType<AddPhotoGalleryImagesCommand> */
final class PhotoGalleryUploadImagesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void { $builder->add('images', FileType::class, ['label' => 'Ajouter des images', 'multiple' => true, 'attr' => ['accept' => 'image/jpeg,image/png,image/webp', 'data-gallery-upload-target' => 'input'], 'constraints' => [new All([new File(maxSize: '5M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'], mimeTypesMessage: 'Seules les images JPEG, PNG et WebP sont acceptées.')])]]); }
    public function configureOptions(OptionsResolver $resolver): void { $resolver->setDefaults(['data_class' => AddPhotoGalleryImagesCommand::class, 'translation_domain' => false]); }
}

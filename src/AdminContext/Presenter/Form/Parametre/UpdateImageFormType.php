<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Presenter\Form\Parametre;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\AdminContext\Application\Usecase\Command\Image\UpdateImageCommand;

/** @extends AbstractType<UpdateImageCommand> */
class UpdateImageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imageFile', FileType::class, [
                'label' => "Image",
                'required' => false,
                'translation_domain' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UpdateImageCommand::class,
            'csrf_token_id' => 'update_image',
        ]);
    }
}

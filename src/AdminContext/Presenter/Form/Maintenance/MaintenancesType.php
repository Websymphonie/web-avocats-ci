<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Presenter\Form\Maintenance;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\AdminContext\Application\Usecase\Command\Maintenance\UpdateMaintenanceCommand;

/** @extends AbstractType<UpdateMaintenanceCommand> */
class MaintenancesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('active', CheckboxType::class, [
            'required' => false,
            'label' => 'Mode maintenance ?',
            'translation_domain' => false,
            'row_attr' => [
                'class' => 'form-check form-switch'
            ],
            'attr' => [
                'class' => 'form-check-input',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UpdateMaintenanceCommand::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'maintenance_update',
        ]);
    }
}

<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Presenter\Form\Reglage;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\AdminContext\Application\Usecase\Command\Reglage\UpdateReglageCommand;

/** @extends AbstractType<UpdateReglageCommand> */
class UpdateReglageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var UpdateReglageCommand|null $data */
            $data = $event->getData();
            $form = $event->getForm();

            if (!$data) {
                return;
            }

            $value = $data->value;

            $valueData = match ($data->type) {
                CheckboxType::class => (bool)$value,
                PercentType::class => (float)$value,
                MoneyType::class => (float)$value,
                default => $value,
            };

            $fieldOptions = [
                'data' => $valueData,
                'required' => false,
                'translation_domain' => false,
                'label' => $data->label,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => $data->label,
                ]
            ];

            // Ajout de l'option "scale" si PercentType
            if ($data->type === PercentType::class) {
                $fieldOptions['scale'] = 2;
                $fieldOptions['type'] = 'integer';
            }
            if ($data->type === MoneyType::class) {
                $fieldOptions['scale'] = 0;
                $fieldOptions['currency'] = 'XOF';
                $fieldOptions['grouping'] = true;
                $fieldOptions['help'] = 'Saisissez un montant entier. Les espaces sont acceptés : 25000000 ou 25 000 000.';
                $fieldOptions['attr']['inputmode'] = 'numeric';
                $fieldOptions['attr']['autocomplete'] = 'off';
                $fieldOptions['attr']['placeholder'] = 'Ex. 25 000 000';
            }

            $form->add('value', $data->type, $fieldOptions);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UpdateReglageCommand::class,
            'csrf_token_id' => 'update_reglage',
        ]);
    }
}

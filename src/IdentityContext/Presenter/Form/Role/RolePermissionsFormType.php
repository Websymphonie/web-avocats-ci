<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Form\Role;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Websymphonie\IdentityContext\Application\Usecase\Command\Role\UpdateRolePermissionsCommand;
use Websymphonie\IdentityContext\Presenter\Service\Role\PermissionCatalog;

/** @extends AbstractType<UpdateRolePermissionsCommand> */
final class RolePermissionsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('permissions', ChoiceType::class, [
            'label' => 'Permissions accordées',
            'choices' => PermissionCatalog::groupedChoices(),
            'expanded' => true,
            'multiple' => true,
            'choice_translation_domain' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UpdateRolePermissionsCommand::class,
            'csrf_token_id' => 'role_permissions_update',
        ]);
    }
}

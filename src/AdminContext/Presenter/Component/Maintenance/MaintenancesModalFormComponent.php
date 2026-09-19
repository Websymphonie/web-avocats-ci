<?php

declare(strict_types=1);

namespace Websymphonie\AdminContext\Presenter\Component\Maintenance;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\AdminContext\Application\Usecase\Command\Maintenance\UpdateMaintenanceCommand;
use Websymphonie\AdminContext\Presenter\Form\Maintenance\MaintenancesType;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;

#[AsTwigComponent(
    name: 'MaintenancesModalFormComponent',
    template: 'admin/maintenance/components/maintenance_modal_form_component.html.twig'
)]
final class MaintenancesModalFormComponent
{
    public FormView $form;

    public int $maintenanceId;

    public function __construct(
        private readonly ContextServiceInterface $context,
        private readonly FormFactoryInterface    $formFactory,
        private readonly UrlGeneratorInterface   $urlGenerator,
    )
    {
    }

    public function mount(): void
    {
        $maintenance = $this->context->findMaintenance();
        $this->maintenanceId = $maintenance->id;

        $command = new UpdateMaintenanceCommand(
            id: $maintenance->id,
            active: $maintenance->active,
        );

        $this->form = $this->formFactory
            ->create(
                MaintenancesType::class,
                $command,
                [
                    'method' => 'POST',
                    'action' => $this->urlGenerator->generate(
                        'admin_maintenance_edit',
                        [
                            'id' => $maintenance->id,
                        ],
                    ),
                ],
            )
            ->createView();
    }
}
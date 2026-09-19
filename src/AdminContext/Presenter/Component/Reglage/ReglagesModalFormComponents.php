<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Presenter\Component\Reglage;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\AdminContext\Application\Usecase\Command\Reglage\UpdateReglageCommand;
use Websymphonie\AdminContext\Domain\Model\Reglage\ReglageModel;
use Websymphonie\AdminContext\Presenter\Form\Reglage\UpdateReglageFormType;

#[AsTwigComponent('ReglagesModalFormComponent', template: 'admin/reglage/component/reglage_modal_form_component.html.twig')]
class ReglagesModalFormComponents extends AbstractController
{
    use ComponentWithFormTrait;

    public ?UpdateReglageCommand $command = null;

    public ?string $type = null;

    public string $id;

    public string $title;

    public ?string $urlPath = 'javascript:';

    public function mount(?ReglageModel $reglage = null): void
    {
        if ($reglage !== null) {
            $this->command = new UpdateReglageCommand(
                id: $reglage->id,
                value: $reglage->value,
                label: $reglage->label,
                type: $reglage->type,
            );
        }
    }

    /** @return FormInterface<UpdateReglageCommand> */
    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(UpdateReglageFormType::class, $this->command);
    }
}

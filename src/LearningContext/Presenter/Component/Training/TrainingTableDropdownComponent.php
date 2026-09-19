<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Component\Training;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\LearningContext\Domain\Model\Training;

#[AsTwigComponent('TrainingTableDropdown', template: 'learning/admin/training/components/table_dropdown_component.html.twig')]
final class TrainingTableDropdownComponent
{
    public Training $training;
    public string $instanceId;

    public function mount(Training $training, string $instanceId = ''): void
    {
        $this->training = $training;
        $this->instanceId = $instanceId !== '' ? $instanceId : 'training-' . $training->id;
    }
}

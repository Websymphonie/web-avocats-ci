<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Component\Training;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('BulkDeleteTrainings', template: 'learning/admin/training/components/bulk_delete_component.html.twig')]
final class BulkDeleteTrainingsComponent
{
    public string $formId = 'training-bulk-delete';
}

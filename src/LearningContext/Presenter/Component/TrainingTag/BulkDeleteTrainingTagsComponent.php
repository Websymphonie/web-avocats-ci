<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Component\TrainingTag;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('BulkDeleteTrainingTags', template: 'learning/admin/taxonomy/bulk_delete_tags_component.html.twig')]
final class BulkDeleteTrainingTagsComponent
{
    public string $formId = 'training-tag-bulk-delete';
}

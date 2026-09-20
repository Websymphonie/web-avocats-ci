<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Component\TrainingCategory;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('BulkDeleteTrainingCategories', template: 'learning/admin/taxonomy/bulk_delete_categories_component.html.twig')]
final class BulkDeleteTrainingCategoriesComponent
{
    public string $formId = 'training-category-bulk-delete';
}

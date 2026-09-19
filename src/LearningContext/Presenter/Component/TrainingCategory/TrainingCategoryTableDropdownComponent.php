<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Presenter\Component\TrainingCategory;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\LearningContext\Domain\Model\TrainingCategory;
#[AsTwigComponent('TrainingCategoryTableDropdown', template: 'learning/admin/taxonomy/category_table_dropdown.html.twig')]
final class TrainingCategoryTableDropdownComponent { public TrainingCategory $category; public string $instanceId; public function mount(TrainingCategory $category, string $instanceId = ''): void { $this->category = $category; $this->instanceId = $instanceId !== '' ? $instanceId : 'training-category-' . $category->id; } }

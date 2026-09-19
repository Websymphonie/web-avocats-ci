<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Presenter\Component\TrainingTag;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\LearningContext\Domain\Model\TrainingTag;
#[AsTwigComponent('TrainingTagTableDropdown', template: 'learning/admin/taxonomy/tag_table_dropdown.html.twig')]
final class TrainingTagTableDropdownComponent { public TrainingTag $tag; public string $instanceId; public function mount(TrainingTag $tag, string $instanceId = ''): void { $this->tag = $tag; $this->instanceId = $instanceId !== '' ? $instanceId : 'training-tag-' . $tag->id; } }

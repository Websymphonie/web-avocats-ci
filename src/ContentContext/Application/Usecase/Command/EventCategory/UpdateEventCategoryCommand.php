<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\EventCategory;

final class UpdateEventCategoryCommand { public function __construct(public int $id, public string $name = '', public ?string $description = null) {} }

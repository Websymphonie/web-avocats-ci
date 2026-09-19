<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\EventCategory;

final class CreateEventCategoryCommand { public function __construct(public string $name = '', public ?string $description = null) {} }

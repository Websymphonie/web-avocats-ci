<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\EventCategory;

final class BulkDeleteEventCategoriesCommand { /** @param list<int> $ids */ public function __construct(public array $ids) {} }

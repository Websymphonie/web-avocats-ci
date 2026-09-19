<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\Event;

final class CancelEventCommand { public function __construct(public int $id) {} }

<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\Command\Tag;
final class CreateTagCommand { public function __construct(public string $name = '') {} }

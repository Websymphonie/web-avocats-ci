<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\Command\Tag;
final class UpdateTagCommand { public function __construct(public int $id, public string $name = '') {} }

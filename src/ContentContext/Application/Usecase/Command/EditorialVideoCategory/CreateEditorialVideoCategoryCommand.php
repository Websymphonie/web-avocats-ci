<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideoCategory;
final class CreateEditorialVideoCategoryCommand { public function __construct(public string $name = '', public ?string $description = null) {} }

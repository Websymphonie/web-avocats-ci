<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\Command\NewsCategory;
final class UpdateNewsCategoryCommand { public function __construct(public int $id, public string $name = '', public ?string $description = null) {} }

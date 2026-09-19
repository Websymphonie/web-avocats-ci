<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\Command\NewsCategory;
final readonly class BulkDeleteNewsCategoriesCommand { /** @param list<int> $ids */ public function __construct(public array $ids) {} }

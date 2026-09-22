<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideoCategory;
final class BulkDeleteEditorialVideoCategoriesCommand { /** @param list<int> $ids */ public function __construct(public array $ids) {} }

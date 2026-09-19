<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\Command\Tag;
final readonly class BulkDeleteTagsCommand { /** @param list<int> $ids */ public function __construct(public array $ids) {} }

<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo;
final class BulkDeleteEditorialVideosCommand { /** @param list<int> $ids */ public function __construct(public array $ids) {} }

<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\Query\EditorialVideo;
use Websymphonie\ContentContext\Domain\Enum\EditorialVideoStatus;
use Websymphonie\ContentContext\Domain\Enum\VideoProvider;
final class GetEditorialVideoListQuery { public function __construct(public ?string $search = null, public ?EditorialVideoStatus $status = null, public ?VideoProvider $provider = null, public ?int $tagId = null, public ?int $categoryId = null, public int $page = 1, public int $limit = 20) {} }

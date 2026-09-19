<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\Query\NewsCategory;
final class GetNewsCategoryListQuery { public function __construct(public ?string $search = null, public int $page = 1, public int $limit = 20) {} }

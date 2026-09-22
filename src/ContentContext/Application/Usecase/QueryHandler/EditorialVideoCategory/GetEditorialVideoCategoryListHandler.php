<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\EditorialVideoCategory;
use Websymphonie\ContentContext\Application\Usecase\Query\EditorialVideoCategory\GetEditorialVideoCategoryListQuery;
use Websymphonie\ContentContext\Domain\Model\EditorialVideoCategoryListResult;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoCategoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;
final readonly class GetEditorialVideoCategoryListHandler implements QueryHandler { public function __construct(private EditorialVideoCategoryRepositoryInterface $repository) {} public function __invoke(GetEditorialVideoCategoryListQuery $query): EditorialVideoCategoryListResult { return $this->repository->list($query->search, $query->page, $query->limit); } }

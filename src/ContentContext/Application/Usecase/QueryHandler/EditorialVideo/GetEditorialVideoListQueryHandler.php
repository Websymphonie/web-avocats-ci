<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\EditorialVideo;
use Websymphonie\ContentContext\Application\Usecase\Query\EditorialVideo\GetEditorialVideoListQuery;
use Websymphonie\ContentContext\Domain\Model\EditorialVideoListResult;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;
final readonly class GetEditorialVideoListQueryHandler implements QueryHandler { public function __construct(private EditorialVideoRepositoryInterface $repository) {} public function __invoke(GetEditorialVideoListQuery $query): EditorialVideoListResult { return $this->repository->list($query->search, $query->status, $query->provider, $query->tagId, $query->categoryId, $query->page, $query->limit); } }

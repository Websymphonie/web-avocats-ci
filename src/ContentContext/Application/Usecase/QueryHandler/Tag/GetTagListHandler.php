<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\Tag;
use Websymphonie\ContentContext\Application\Usecase\Query\Tag\GetTagListQuery;
use Websymphonie\ContentContext\Domain\Model\TagListResult;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;
final readonly class GetTagListHandler implements QueryHandler
{
    public function __construct(private TagRepositoryInterface $repository) {}
    public function __invoke(GetTagListQuery $query): TagListResult { return $this->repository->list($query->search ?: null, max(1, $query->page), $query->limit); }
}

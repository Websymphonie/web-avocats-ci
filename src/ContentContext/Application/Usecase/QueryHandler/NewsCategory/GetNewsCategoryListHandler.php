<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\NewsCategory;
use Websymphonie\ContentContext\Application\Usecase\Query\NewsCategory\GetNewsCategoryListQuery;
use Websymphonie\ContentContext\Domain\Model\NewsCategoryListResult;
use Websymphonie\ContentContext\Domain\Repository\NewsCategoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;
final readonly class GetNewsCategoryListHandler implements QueryHandler
{
    public function __construct(private NewsCategoryRepositoryInterface $repository) {}
    public function __invoke(GetNewsCategoryListQuery $query): NewsCategoryListResult { return $this->repository->list($query->search ?: null, max(1, $query->page), $query->limit); }
}

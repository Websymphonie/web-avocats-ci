<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\EditorialVideo;
use Websymphonie\ContentContext\Application\Usecase\Query\EditorialVideo\GetEditorialVideoDetailsQuery;
use Websymphonie\ContentContext\Domain\Model\EditorialVideo;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;
final readonly class GetEditorialVideoDetailsQueryHandler implements QueryHandler { public function __construct(private EditorialVideoRepositoryInterface $repository) {} public function __invoke(GetEditorialVideoDetailsQuery $query): EditorialVideo { return $this->repository->getById($query->id); } }

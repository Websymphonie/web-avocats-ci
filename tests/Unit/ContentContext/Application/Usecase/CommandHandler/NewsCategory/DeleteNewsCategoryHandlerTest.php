<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Unit\ContentContext\Application\Usecase\CommandHandler\NewsCategory;

use PHPUnit\Framework\TestCase;
use Websymphonie\ContentContext\Application\Usecase\Command\NewsCategory\DeleteNewsCategoryCommand;
use Websymphonie\ContentContext\Application\Usecase\CommandHandler\NewsCategory\DeleteNewsCategoryHandler;
use Websymphonie\ContentContext\Domain\Exception\NewsCategoryInUseException;
use Websymphonie\ContentContext\Domain\Model\NewsCategory;
use Websymphonie\ContentContext\Domain\Repository\NewsCategoryRepositoryInterface;

final class DeleteNewsCategoryHandlerTest extends TestCase
{
    public function testUsedCategoryIsRefusedBeforeDelete(): void
    {
        $category = new NewsCategory(10, 'uuid', 'Actualités', 'actualites');
        $repository = $this->createMock(NewsCategoryRepositoryInterface::class);
        $repository->expects(self::once())->method('getById')->with(10)->willReturn($category);
        $repository->expects(self::once())->method('countNewsUsage')->with(10)->willReturn(2);
        $repository->expects(self::never())->method('delete');

        $this->expectException(NewsCategoryInUseException::class);
        (new DeleteNewsCategoryHandler($repository))(new DeleteNewsCategoryCommand(10));
    }
}

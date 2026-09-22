<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Unit\ContentContext\Application\Usecase\CommandHandler\EditorialVideoCategory;

use PHPUnit\Framework\TestCase;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideoCategory\DeleteEditorialVideoCategoryCommand;
use Websymphonie\ContentContext\Application\Usecase\CommandHandler\EditorialVideoCategory\DeleteEditorialVideoCategoryHandler;
use Websymphonie\ContentContext\Domain\Exception\EditorialVideoCategoryInUseException;
use Websymphonie\ContentContext\Domain\Model\EditorialVideoCategory;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoCategoryRepositoryInterface;

final class DeleteEditorialVideoCategoryHandlerTest extends TestCase
{
    public function testUsedCategoryIsRefusedBeforeDelete(): void
    {
        $category = new EditorialVideoCategory(10, 'uuid', 'Profession', 'profession');
        $repository = $this->createMock(EditorialVideoCategoryRepositoryInterface::class);
        $repository->expects(self::once())->method('getById')->with(10)->willReturn($category);
        $repository->expects(self::once())->method('countVideoUsage')->with(10)->willReturn(2);
        $repository->expects(self::never())->method('delete');

        $this->expectException(EditorialVideoCategoryInUseException::class);
        (new DeleteEditorialVideoCategoryHandler($repository))(new DeleteEditorialVideoCategoryCommand(10));
    }
}

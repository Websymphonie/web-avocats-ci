<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Unit\ContentContext\Application\Usecase\CommandHandler\Tag;

use PHPUnit\Framework\TestCase;
use Websymphonie\ContentContext\Application\Usecase\Command\Tag\DeleteTagCommand;
use Websymphonie\ContentContext\Application\Usecase\CommandHandler\Tag\DeleteTagHandler;
use Websymphonie\ContentContext\Domain\Exception\TagInUseException;
use Websymphonie\ContentContext\Domain\Model\Tag;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;

final class DeleteTagHandlerTest extends TestCase
{
    public function testTagUsedByAnEditorialVideoIsRefusedBeforeDelete(): void
    {
        $tag = new Tag(10, 'uuid', 'Interview', 'interview');
        $repository = $this->createMock(TagRepositoryInterface::class);
        $repository->expects(self::once())->method('getById')->with(10)->willReturn($tag);
        $repository->expects(self::once())->method('countNewsUsage')->with(10)->willReturn(1);
        $repository->expects(self::never())->method('delete');
        $this->expectException(TagInUseException::class);
        (new DeleteTagHandler($repository))(new DeleteTagCommand(10));
    }
}

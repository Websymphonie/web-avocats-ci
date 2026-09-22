<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Unit\ContentContext\Application\Usecase\CommandHandler\EditorialVideo;

use PHPUnit\Framework\TestCase;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo\ArchiveEditorialVideoCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo\BulkDeleteEditorialVideosCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo\CreateEditorialVideoCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo\DeleteEditorialVideoCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo\PublishEditorialVideoCommand;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo\UpdateEditorialVideoCommand;
use Websymphonie\ContentContext\Application\Usecase\CommandHandler\EditorialVideo\ArchiveEditorialVideoHandler;
use Websymphonie\ContentContext\Application\Usecase\CommandHandler\EditorialVideo\BulkDeleteEditorialVideosHandler;
use Websymphonie\ContentContext\Application\Usecase\CommandHandler\EditorialVideo\CreateEditorialVideoHandler;
use Websymphonie\ContentContext\Application\Usecase\CommandHandler\EditorialVideo\DeleteEditorialVideoHandler;
use Websymphonie\ContentContext\Application\Usecase\CommandHandler\EditorialVideo\PublishEditorialVideoHandler;
use Websymphonie\ContentContext\Application\Usecase\CommandHandler\EditorialVideo\UpdateEditorialVideoHandler;
use Websymphonie\ContentContext\Domain\Enum\EditorialVideoStatus;
use Websymphonie\ContentContext\Domain\Model\EditorialVideo;
use Websymphonie\ContentContext\Domain\Model\EditorialVideoCategory;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoCategoryRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;

final class EditorialVideoHandlersTest extends TestCase
{
    public function testCreateAndUpdateHandlersSanitizeAndPersist(): void
    {
        $repository = $this->createMock(EditorialVideoRepositoryInterface::class);
        $tags = $this->createMock(TagRepositoryInterface::class);
        $categories = $this->createMock(EditorialVideoCategoryRepositoryInterface::class);
        $sanitizer = $this->createMock(RichTextSanitizerInterface::class);
        $sanitizer->expects(self::exactly(2))->method('sanitize')->willReturnArgument(0);
        $tags->expects(self::exactly(2))->method('findByIds')->willReturn([]);
        $category = new EditorialVideoCategory(5, 'category-uuid', 'Profession', 'profession');
        $categories->expects(self::exactly(2))->method('getById')->with(5)->willReturn($category);
        $repository->method('slugExists')->willReturn(false);
        $repository->expects(self::exactly(2))->method('save')->willReturnArgument(0);
        $create = new CreateEditorialVideoHandler($repository, $tags, $categories, $sanitizer, new AsciiSlugger('en'));
        $video = $create(new CreateEditorialVideoCommand(title: 'Première vidéo', description: '<p>Texte</p>', videoUrl: 'https://youtu.be/abcDEF_123', categoryId: 5));
        self::assertSame('premiere-video', $video->slug);
        $update = new UpdateEditorialVideoHandler($repository, $tags, $categories, $sanitizer, new AsciiSlugger('en'));
        $repository->method('getById')->willReturn($video);
        $update(new UpdateEditorialVideoCommand(1, 'Vidéo modifiée', description: '<p>Autre</p>', videoUrl: 'https://youtu.be/abcDEF_123', categoryId: 5));
        self::assertSame('video-modifiee', $video->slug);
    }

    public function testPublishAndArchiveHandlersDelegateLifecycleTransitions(): void
    {
        $video = $this->video();
        $repository = $this->createMock(EditorialVideoRepositoryInterface::class);
        $repository->expects(self::exactly(2))->method('getById')->with(1)->willReturn($video);
        $repository->expects(self::exactly(2))->method('save')->with($video)->willReturn($video);
        (new PublishEditorialVideoHandler($repository))(new PublishEditorialVideoCommand(1));
        (new ArchiveEditorialVideoHandler($repository))(new ArchiveEditorialVideoCommand(1));
        self::assertSame(EditorialVideoStatus::ARCHIVED, $video->status);
    }

    public function testDeleteAndBulkDeleteHandlersDelegateToRepository(): void
    {
        $first = $this->video(1);
        $second = $this->video(2);
        $repository = $this->createMock(EditorialVideoRepositoryInterface::class);
        $repository->expects(self::once())->method('getById')->with(1)->willReturn($first);
        $repository->expects(self::once())->method('findByIds')->with([1, 2])->willReturn([$first, $second]);
        $repository->expects(self::exactly(3))->method('delete')->withConsecutive([$first], [$first], [$second]);
        (new DeleteEditorialVideoHandler($repository))(new DeleteEditorialVideoCommand(1));
        (new BulkDeleteEditorialVideosHandler($repository))(new BulkDeleteEditorialVideosCommand([1, 2]));
    }

    private function video(int $id = 1): EditorialVideo
    {
        return new EditorialVideo($id, 'uuid-' . $id, 'Vidéo', 'video-' . $id, null, '', \Websymphonie\ContentContext\Domain\Enum\VideoProvider::YOUTUBE, 'https://youtu.be/abcDEF_123');
    }
}

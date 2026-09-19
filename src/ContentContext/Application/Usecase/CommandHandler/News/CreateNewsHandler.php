<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\News;

use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\News\CreateNewsCommand;
use Websymphonie\ContentContext\Domain\Exception\NewsSlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Model\News;
use Websymphonie\ContentContext\Domain\Repository\NewsCategoryRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\NewsRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\PhotoGalleryRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\MediaUploadServiceInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class CreateNewsHandler implements CommandHandler
{
    public function __construct(private NewsRepositoryInterface $repository, private NewsCategoryRepositoryInterface $categoryRepository, private TagRepositoryInterface $tagRepository, private PhotoGalleryRepositoryInterface $galleryRepository, private RichTextSanitizerInterface $richTextSanitizer, private SluggerInterface $slugger, private MediaUploadServiceInterface $mediaUpload) {}

    public function __invoke(CreateNewsCommand $command): News
    {
        $slug = strtolower($this->slugger->slug($command->title)->toString());
        if ($slug === '' || $this->repository->slugExists($slug)) {
            throw NewsSlugAlreadyExistsException::withSlug($slug ?: $command->title);
        }

        $galleryId = $command->photoGalleryId !== null ? $this->galleryRepository->getById($command->photoGalleryId)->id : null;
        $media = null;
        try {
            $media = $command->cover !== null ? $this->mediaUpload->upload($command->cover, 'content/covers') : null;
            $news = new News(0, '', trim($command->title), $slug, $command->excerpt ?: null, $this->richTextSanitizer->sanitize($command->body), coverMediaId: $media?->id, photoGalleryId: $galleryId);
            $news->replaceCategories($this->categoryRepository->findByIds($command->categories));
            $news->replaceTags($this->tagRepository->findByIds($command->tags));
            return $this->repository->save($news);
        } catch (\Throwable $exception) {
            if ($media !== null) { try { $this->mediaUpload->delete($media); } catch (\Throwable) {} }
            throw $exception;
        }
    }
}

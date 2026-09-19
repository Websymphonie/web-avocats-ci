<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\PhotoGallery;

use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery\CreatePhotoGalleryCommand;
use Websymphonie\ContentContext\Domain\Exception\PhotoGallerySlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Model\PhotoGallery;
use Websymphonie\ContentContext\Domain\Repository\PhotoGalleryRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\MediaUploadServiceInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class CreatePhotoGalleryHandler implements CommandHandler
{
    public function __construct(private PhotoGalleryRepositoryInterface $repository, private TagRepositoryInterface $tagRepository, private RichTextSanitizerInterface $sanitizer, private SluggerInterface $slugger, private MediaUploadServiceInterface $mediaUpload) {}
    public function __invoke(CreatePhotoGalleryCommand $command): PhotoGallery
    {
        $slug = strtolower($this->slugger->slug($command->title)->toString());
        if ($slug === '' || $this->repository->slugExists($slug)) { throw PhotoGallerySlugAlreadyExistsException::withSlug($slug ?: $command->title); }
        $gallery = new PhotoGallery(0, '', trim($command->title), $slug, $this->sanitizer->sanitize($command->description));
        $gallery->tags = $this->tagRepository->findByIds($command->tags);
        $media = [];
        try {
            foreach ($command->images as $image) { $media[] = $this->mediaUpload->upload($image); }
            $gallery->addMedia(array_map(static fn ($asset): int => $asset->id, $media));
            return $this->repository->save($gallery);
        } catch (\Throwable $exception) {
            foreach ($media as $asset) { try { $this->mediaUpload->delete($asset); } catch (\Throwable) {} }
            throw $exception;
        }
    }
}

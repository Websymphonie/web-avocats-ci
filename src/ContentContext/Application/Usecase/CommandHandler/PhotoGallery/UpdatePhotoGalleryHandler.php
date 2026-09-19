<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\PhotoGallery;

use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\PhotoGallery\UpdatePhotoGalleryCommand;
use Websymphonie\ContentContext\Domain\Exception\PhotoGallerySlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Model\PhotoGallery;
use Websymphonie\ContentContext\Domain\Repository\PhotoGalleryRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class UpdatePhotoGalleryHandler implements CommandHandler
{
    public function __construct(private PhotoGalleryRepositoryInterface $repository, private TagRepositoryInterface $tagRepository, private RichTextSanitizerInterface $sanitizer, private SluggerInterface $slugger) {}
    public function __invoke(UpdatePhotoGalleryCommand $command): PhotoGallery
    {
        $gallery = $this->repository->getById($command->id);
        $slug = strtolower($this->slugger->slug($command->title)->toString());
        if ($gallery->status->value === 'DRAFT' && ($slug === '' || $this->repository->slugExists($slug, $gallery->id))) { throw PhotoGallerySlugAlreadyExistsException::withSlug($slug ?: $command->title); }
        $gallery->update($command->title, $slug, $this->sanitizer->sanitize($command->description), $this->tagRepository->findByIds($command->tags));
        foreach ($command->items as $mediaId => $metadata) { $gallery->updateItemMetadata((int) $mediaId, (string) ($metadata['altText'] ?? ''), isset($metadata['caption']) ? (string) $metadata['caption'] : null); }
        return $this->repository->save($gallery);
    }
}

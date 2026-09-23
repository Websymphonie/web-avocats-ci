<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Page;

use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\Page\UpdatePageCommand;
use Websymphonie\ContentContext\Domain\Exception\PageSlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Model\Page;
use Websymphonie\ContentContext\Domain\Repository\PageRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\MediaUploadServiceInterface;
use Websymphonie\MediaContext\Domain\Exception\MediaInUseException;
use Websymphonie\MediaContext\Domain\Repository\MediaRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class UpdatePageHandler implements CommandHandler
{
    public function __construct(
        private PageRepositoryInterface $repository,
        private RichTextSanitizerInterface $sanitizer,
        private SluggerInterface $slugger,
        private MediaUploadServiceInterface $mediaUpload,
        private MediaRepositoryInterface $mediaRepository,
    ) {
    }

    public function __invoke(UpdatePageCommand $command): Page
    {
        $page = $this->repository->getById($command->id);
        $slug = strtolower($this->slugger->slug(trim($command->slug !== '' ? $command->slug : $command->title))->toString());
        if ($this->repository->slugExists($slug, $page->id, $command->group)) {
            throw new PageSlugAlreadyExistsException(sprintf('Le slug « %s » est déjà utilisé.', $slug));
        }

        $oldCoverId = $page->coverMediaId;
        $media = null;
        try {
            $media = $command->cover !== null ? $this->mediaUpload->upload($command->cover, 'content/covers') : null;
            $page->update(trim($command->title), $slug, $this->sanitizer->sanitize($command->content));
            $page->setGroup($command->group);
            $page->setSortOrder($command->sortOrder);
            if ($media !== null) {
                $page->setCoverMedia($media->id);
            } elseif ($command->removeCover) {
                $page->setCoverMedia(null);
            }
            $saved = $this->repository->save($page);
            if ($oldCoverId !== null && (($media !== null) || $command->removeCover)) {
                $this->removeIfOrphaned($oldCoverId);
            }
            return $saved;
        } catch (\Throwable $exception) {
            if ($media !== null) {
                try { $this->mediaUpload->delete($media); } catch (\Throwable) {}
            }
            throw $exception;
        }
    }

    private function removeIfOrphaned(int $mediaId): void
    {
        try { $this->mediaUpload->delete($this->mediaRepository->getById($mediaId)); } catch (MediaInUseException) {}
    }
}

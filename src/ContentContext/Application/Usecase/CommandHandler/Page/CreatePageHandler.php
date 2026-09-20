<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Page;

use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\Page\CreatePageCommand;
use Websymphonie\ContentContext\Domain\Exception\PageSlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Model\Page;
use Websymphonie\ContentContext\Domain\Repository\PageRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\MediaUploadServiceInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class CreatePageHandler implements CommandHandler
{
    public function __construct(
        private PageRepositoryInterface $repository,
        private RichTextSanitizerInterface $sanitizer,
        private SluggerInterface $slugger,
        private MediaUploadServiceInterface $mediaUpload,
    ) {
    }

    public function __invoke(CreatePageCommand $command): Page
    {
        $slug = $this->normalizeSlug($command->slug !== '' ? $command->slug : $command->title);
        if ($this->repository->slugExists($slug)) {
            throw new PageSlugAlreadyExistsException(sprintf('Le slug « %s » est déjà utilisé.', $slug));
        }

        $media = null;
        try {
            $media = $command->cover !== null ? $this->mediaUpload->upload($command->cover, 'content/covers') : null;
            return $this->repository->save(new Page(0, '', trim($command->title), $slug, $this->sanitizer->sanitize($command->content), coverMediaId: $media?->id));
        } catch (\Throwable $exception) {
            if ($media !== null) {
                try { $this->mediaUpload->delete($media); } catch (\Throwable) {}
            }
            throw $exception;
        }
    }

    private function normalizeSlug(string $value): string
    {
        return strtolower($this->slugger->slug(trim($value))->toString());
    }
}

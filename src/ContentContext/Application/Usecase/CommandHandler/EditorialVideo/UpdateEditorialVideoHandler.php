<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\EditorialVideo;

use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo\UpdateEditorialVideoCommand;
use Websymphonie\ContentContext\Domain\Enum\EditorialVideoStatus;
use Websymphonie\ContentContext\Domain\Exception\EditorialVideoSlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class UpdateEditorialVideoHandler implements CommandHandler
{
    public function __construct(private EditorialVideoRepositoryInterface $repository, private TagRepositoryInterface $tagRepository, private RichTextSanitizerInterface $sanitizer, private SluggerInterface $slugger) {}
    public function __invoke(UpdateEditorialVideoCommand $command): void
    {
        $video = $this->repository->getById($command->id);
        $slug = strtolower($this->slugger->slug($command->title)->toString());
        if ($video->status === EditorialVideoStatus::DRAFT && ($slug === '' || $this->repository->slugExists($slug, $video->id))) { throw EditorialVideoSlugAlreadyExistsException::withSlug($slug ?: $command->title); }
        $video->update(trim($command->title), $slug ?: $video->slug, self::clean($command->excerpt), $this->sanitizer->sanitize($command->description), $command->provider, trim($command->videoUrl), $this->tagRepository->findByIds($command->tags));
        $this->repository->save($video);
    }
    private static function clean(?string $value): ?string { $value = $value !== null ? trim($value) : null; return $value === '' ? null : $value; }
}

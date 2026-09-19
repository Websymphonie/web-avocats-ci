<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\EditorialVideo;

use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo\CreateEditorialVideoCommand;
use Websymphonie\ContentContext\Domain\Exception\EditorialVideoSlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Model\EditorialVideo;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class CreateEditorialVideoHandler implements CommandHandler
{
    public function __construct(private EditorialVideoRepositoryInterface $repository, private TagRepositoryInterface $tagRepository, private RichTextSanitizerInterface $sanitizer, private SluggerInterface $slugger) {}
    public function __invoke(CreateEditorialVideoCommand $command): EditorialVideo
    {
        $slug = strtolower($this->slugger->slug($command->title)->toString());
        if ($slug === '' || $this->repository->slugExists($slug)) { throw EditorialVideoSlugAlreadyExistsException::withSlug($slug ?: $command->title); }
        $video = new EditorialVideo(0, '', trim($command->title), $slug, self::clean($command->excerpt), $this->sanitizer->sanitize($command->description), $command->provider, trim($command->videoUrl));
        $video->replaceTags($this->tagRepository->findByIds($command->tags));
        return $this->repository->save($video);
    }
    private static function clean(?string $value): ?string { $value = $value !== null ? trim($value) : null; return $value === '' ? null : $value; }
}

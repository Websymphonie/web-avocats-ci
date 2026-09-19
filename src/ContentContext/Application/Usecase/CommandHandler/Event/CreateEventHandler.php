<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Event;

use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\Event\CreateEventCommand;
use Websymphonie\ContentContext\Domain\Exception\EventSlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Exception\InvalidEventDetailsException;
use Websymphonie\ContentContext\Domain\Model\Event;
use Websymphonie\ContentContext\Domain\Repository\EventCategoryRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\EventRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class CreateEventHandler implements CommandHandler
{
    public function __construct(private EventRepositoryInterface $repository, private EventCategoryRepositoryInterface $categoryRepository, private TagRepositoryInterface $tagRepository, private RichTextSanitizerInterface $sanitizer, private SluggerInterface $slugger) {}
    public function __invoke(CreateEventCommand $command): Event
    {
        $slug = strtolower($this->slugger->slug($command->title)->toString());
        if ($slug === '' || $this->repository->slugExists($slug)) { throw EventSlugAlreadyExistsException::withSlug($slug ?: $command->title); }
        $event = new Event(0, '', trim($command->title), $slug, $command->excerpt ?: null, $this->sanitizer->sanitize($command->description), $command->format, $command->startsAt ?? throw new InvalidEventDetailsException('La date de début est requise.'), $command->endsAt, self::clean($command->venueName), self::clean($command->address), self::clean($command->onlineUrl));
        $event->replaceCategories($this->categoryRepository->findByIds($command->categories));
        $event->replaceTags($this->tagRepository->findByIds($command->tags));
        return $this->repository->save($event);
    }
    private static function clean(?string $value): ?string { $value = $value !== null ? trim($value) : null; return $value === '' ? null : $value; }
}

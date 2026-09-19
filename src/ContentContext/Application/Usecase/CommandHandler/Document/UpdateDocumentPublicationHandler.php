<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Document;

use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\Document\UpdateDocumentPublicationCommand;
use Websymphonie\ContentContext\Domain\Exception\DocumentSlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Repository\DocumentPublicationRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class UpdateDocumentPublicationHandler implements CommandHandler
{
    public function __construct(private DocumentPublicationRepositoryInterface $repository, private TagRepositoryInterface $tagRepository, private RichTextSanitizerInterface $sanitizer, private SluggerInterface $slugger) {}
    public function __invoke(UpdateDocumentPublicationCommand $command): \Websymphonie\ContentContext\Domain\Model\DocumentPublication
    {
        $document = $this->repository->getById($command->id);
        $slug = strtolower($this->slugger->slug($command->title)->toString());
        if ($document->status->value === 'DRAFT' && ($slug === '' || $this->repository->slugExists($slug, $document->id))) { throw new DocumentSlugAlreadyExistsException(sprintf('Le slug « %s » est déjà utilisé.', $slug ?: $command->title)); }
        $document->update($command->title, $slug, $this->sanitizer->sanitize($command->description), $command->accessLevel, $this->tagRepository->findByIds($command->tags));
        return $this->repository->save($document);
    }
}

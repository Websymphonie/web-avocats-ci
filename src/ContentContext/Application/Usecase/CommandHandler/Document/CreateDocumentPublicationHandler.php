<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Document;

use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\Document\CreateDocumentPublicationCommand;
use Websymphonie\ContentContext\Domain\Exception\DocumentSlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Model\DocumentPublication;
use Websymphonie\ContentContext\Domain\Repository\DocumentPublicationRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\StoredFileUploadServiceInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class CreateDocumentPublicationHandler implements CommandHandler
{
    public function __construct(private DocumentPublicationRepositoryInterface $repository, private TagRepositoryInterface $tagRepository, private RichTextSanitizerInterface $sanitizer, private SluggerInterface $slugger, private StoredFileUploadServiceInterface $files) {}
    public function __invoke(CreateDocumentPublicationCommand $command): DocumentPublication
    {
        if (!$command->file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) { throw new \Websymphonie\MediaContext\Domain\Exception\InvalidStoredFileException('Un fichier est obligatoire.'); }
        $slug = strtolower($this->slugger->slug($command->title)->toString());
        if ($slug === '' || $this->repository->slugExists($slug)) { throw new DocumentSlugAlreadyExistsException(sprintf('Le slug « %s » est déjà utilisé.', $slug ?: $command->title)); }
        $file = $this->files->upload($command->file);
        $document = new DocumentPublication(0, '', trim($command->title), $slug, $this->sanitizer->sanitize($command->description), $file->id, $command->accessLevel, tags: $this->tagRepository->findByIds($command->tags));
        try { return $this->repository->save($document); } catch (\Throwable $exception) { try { $this->files->delete($file); } catch (\Throwable) {} throw $exception; }
    }
}

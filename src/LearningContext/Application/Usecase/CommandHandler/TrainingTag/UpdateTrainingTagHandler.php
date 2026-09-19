<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler\TrainingTag;
use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\LearningContext\Application\Usecase\Command\TrainingTag\UpdateTrainingTagCommand;
use Websymphonie\LearningContext\Domain\Exception\TrainingTaxonomySlugAlreadyExistsException;
use Websymphonie\LearningContext\Domain\Repository\TrainingTagRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class UpdateTrainingTagHandler implements CommandHandler { public function __construct(private TrainingTagRepositoryInterface $repository, private SluggerInterface $slugger) {} public function __invoke(UpdateTrainingTagCommand $command): void { $tag = $this->repository->getById($command->id); $name = trim($command->name); $slug = strtolower($this->slugger->slug($name)->toString()); if ($slug === '' || $this->repository->slugExists($slug, $tag->id)) { throw TrainingTaxonomySlugAlreadyExistsException::withSlug($slug ?: $name); } $tag->update($name, $slug); $this->repository->save($tag); } }

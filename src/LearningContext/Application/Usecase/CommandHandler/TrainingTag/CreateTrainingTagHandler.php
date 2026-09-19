<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler\TrainingTag;
use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\LearningContext\Application\Usecase\Command\TrainingTag\CreateTrainingTagCommand;
use Websymphonie\LearningContext\Domain\Exception\TrainingTaxonomySlugAlreadyExistsException;
use Websymphonie\LearningContext\Domain\Model\TrainingTag;
use Websymphonie\LearningContext\Domain\Repository\TrainingTagRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class CreateTrainingTagHandler implements CommandHandler { public function __construct(private TrainingTagRepositoryInterface $repository, private SluggerInterface $slugger) {} public function __invoke(CreateTrainingTagCommand $command): TrainingTag { $name = trim($command->name); $slug = strtolower($this->slugger->slug($name)->toString()); if ($slug === '' || $this->repository->slugExists($slug)) { throw TrainingTaxonomySlugAlreadyExistsException::withSlug($slug ?: $name); } return $this->repository->save(new TrainingTag(0, '', $name, $slug)); } }

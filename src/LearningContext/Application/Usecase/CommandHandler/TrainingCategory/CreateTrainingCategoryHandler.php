<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler\TrainingCategory;
use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\LearningContext\Application\Usecase\Command\TrainingCategory\CreateTrainingCategoryCommand;
use Websymphonie\LearningContext\Domain\Exception\TrainingTaxonomySlugAlreadyExistsException;
use Websymphonie\LearningContext\Domain\Model\TrainingCategory;
use Websymphonie\LearningContext\Domain\Repository\TrainingCategoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class CreateTrainingCategoryHandler implements CommandHandler { public function __construct(private TrainingCategoryRepositoryInterface $repository, private SluggerInterface $slugger) {} public function __invoke(CreateTrainingCategoryCommand $command): TrainingCategory { $name = trim($command->name); $slug = strtolower($this->slugger->slug($name)->toString()); if ($slug === '' || $this->repository->slugExists($slug)) { throw TrainingTaxonomySlugAlreadyExistsException::withSlug($slug ?: $name); } return $this->repository->save(new TrainingCategory(0, '', $name, $slug)); } }

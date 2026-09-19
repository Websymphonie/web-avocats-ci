<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler\TrainingCategory;
use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\LearningContext\Application\Usecase\Command\TrainingCategory\UpdateTrainingCategoryCommand;
use Websymphonie\LearningContext\Domain\Exception\TrainingTaxonomySlugAlreadyExistsException;
use Websymphonie\LearningContext\Domain\Repository\TrainingCategoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class UpdateTrainingCategoryHandler implements CommandHandler { public function __construct(private TrainingCategoryRepositoryInterface $repository, private SluggerInterface $slugger) {} public function __invoke(UpdateTrainingCategoryCommand $command): void { $category = $this->repository->getById($command->id); $name = trim($command->name); $slug = strtolower($this->slugger->slug($name)->toString()); if ($slug === '' || $this->repository->slugExists($slug, $category->id)) { throw TrainingTaxonomySlugAlreadyExistsException::withSlug($slug ?: $name); } $category->update($name, $slug); $this->repository->save($category); } }

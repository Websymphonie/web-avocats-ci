<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Presenter\Controller\TrainingCategory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Command\TrainingCategory\UpdateTrainingCategoryCommand;
use Websymphonie\LearningContext\Domain\Repository\TrainingCategoryRepositoryInterface;
use Websymphonie\LearningContext\Presenter\Form\Taxonomy\TrainingCategoryFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/categories', name: 'learning_admin_category_')]
#[IsGranted('LEARNING_CATEGORY_MANAGE')]
final class UpdateTrainingCategoryController extends AbstractController { public function __construct(private readonly TrainingCategoryRepositoryInterface $repository) {} #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])] public function __invoke(Request $request, int $id): Response { $category = $this->repository->getById($id); $command = new UpdateTrainingCategoryCommand($category->id, $category->name); $form = $this->createForm(TrainingCategoryFormType::class, $command); $form->handleRequest($request); if ($form->isSubmitted() && $form->isValid()) { try { $this->handleCommand($command); $this->flash()->success('Catégorie de formation modifiée.'); return $this->redirectToRoute('learning_admin_category_list'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } } return $this->render('learning/admin/taxonomy/form.html.twig', ['form' => $form->createView(), 'title' => 'Modifier la catégorie de formation', 'backRoute' => 'learning_admin_category_list']); } }

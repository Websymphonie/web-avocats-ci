<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Presenter\Controller\TrainingCategory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Query\TrainingCategory\GetTrainingCategoryListQuery;
use Websymphonie\LearningContext\Presenter\Form\Taxonomy\TrainingCategoryFilterType;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/categories', name: 'learning_admin_category_')]
#[IsGranted('LEARNING_CATEGORY_VIEW')]
final class GetTrainingCategoryListController extends AbstractController { #[Route('', name: 'list', methods: ['GET'])] public function __invoke(Request $request): Response { $query = new GetTrainingCategoryListQuery(page: max(1, $request->query->getInt('page', 1))); $form = $this->createForm(TrainingCategoryFilterType::class, $query, ['method' => 'GET', 'action' => $this->generateUrl('learning_admin_category_list')]); $form->handleRequest($request); return $this->render('learning/admin/taxonomy/category_index.html.twig', ['categories' => $this->handleQuery(new GetTrainingCategoryListQuery($query->search ?: null, $query->page, 20)), 'filterForm' => $form->createView()]); } }

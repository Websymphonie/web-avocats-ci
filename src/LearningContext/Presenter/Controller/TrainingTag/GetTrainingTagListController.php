<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Presenter\Controller\TrainingTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Query\TrainingTag\GetTrainingTagListQuery;
use Websymphonie\LearningContext\Presenter\Form\Taxonomy\TrainingTagFilterType;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/tags', name: 'learning_admin_tag_')]
#[IsGranted('LEARNING_TAG_VIEW')]
final class GetTrainingTagListController extends AbstractController { #[Route('', name: 'list', methods: ['GET'])] public function __invoke(Request $request): Response { $query = new GetTrainingTagListQuery(page: max(1, $request->query->getInt('page', 1))); $form = $this->createForm(TrainingTagFilterType::class, $query, ['method' => 'GET', 'action' => $this->generateUrl('learning_admin_tag_list')]); $form->handleRequest($request); return $this->render('learning/admin/taxonomy/tag_index.html.twig', ['tags' => $this->handleQuery(new GetTrainingTagListQuery($query->search ?: null, $query->page, 20)), 'filterForm' => $form->createView()]); } }

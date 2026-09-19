<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Presenter\Controller\TrainingTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Command\TrainingTag\UpdateTrainingTagCommand;
use Websymphonie\LearningContext\Domain\Repository\TrainingTagRepositoryInterface;
use Websymphonie\LearningContext\Presenter\Form\Taxonomy\TrainingTagFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/tags', name: 'learning_admin_tag_')]
#[IsGranted('LEARNING_TAG_MANAGE')]
final class UpdateTrainingTagController extends AbstractController { public function __construct(private readonly TrainingTagRepositoryInterface $repository) {} #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])] public function __invoke(Request $request, int $id): Response { $tag = $this->repository->getById($id); $command = new UpdateTrainingTagCommand($tag->id, $tag->name); $form = $this->createForm(TrainingTagFormType::class, $command); $form->handleRequest($request); if ($form->isSubmitted() && $form->isValid()) { try { $this->handleCommand($command); $this->flash()->success('Tag de formation modifié.'); return $this->redirectToRoute('learning_admin_tag_list'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } } return $this->render('learning/admin/taxonomy/form.html.twig', ['form' => $form->createView(), 'title' => 'Modifier le tag de formation', 'backRoute' => 'learning_admin_tag_list']); } }

<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Presenter\Controller\TrainingTag;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Command\TrainingTag\CreateTrainingTagCommand;
use Websymphonie\LearningContext\Presenter\Form\Taxonomy\TrainingTagFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/tags', name: 'learning_admin_tag_')]
#[IsGranted('LEARNING_TAG_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::TAG_TRAININGS)]
final class CreateTrainingTagController extends AbstractController { #[Route('/new', name: 'new', methods: ['GET', 'POST'])] public function __invoke(Request $request): Response { $command = new CreateTrainingTagCommand(); $form = $this->createForm(TrainingTagFormType::class, $command); $form->handleRequest($request); if ($form->isSubmitted() && $form->isValid()) { try { $this->handleCommand($command); $this->flash()->success('Tag de formation créé.'); return $this->redirectToRoute('learning_admin_tag_list'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } } return $this->render('learning/admin/taxonomy/form.html.twig', ['form' => $form->createView(), 'title' => 'Nouveau tag de formation', 'backRoute' => 'learning_admin_tag_list']); } }

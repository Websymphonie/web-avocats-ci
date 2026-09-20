<?php
declare(strict_types=1);
namespace Websymphonie\LearningContext\Presenter\Controller\TrainingTag;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Command\TrainingTag\DeleteTrainingTagCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/tags', name: 'learning_admin_tag_')]
#[IsGranted('LEARNING_TAG_DELETE')]
#[HasGroupAccess(RoleGroupEnum::TAG_TRAININGS)]
final class DeleteTrainingTagController extends AbstractController { #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\\d+'], methods: ['DELETE'])] public function __invoke(Request $request, int $id): Response { if (!$this->isCsrfTokenValid('delete' . $id, (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); } try { $this->handleCommand(new DeleteTrainingTagCommand($id)); $this->flash()->success('Tag de formation supprimé.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } return $this->redirectToRoute('learning_admin_tag_list'); } }

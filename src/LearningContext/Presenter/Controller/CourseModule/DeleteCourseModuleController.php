<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\CourseModule;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Command\CourseModule\DeleteCourseModuleCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings/{trainingId}/modules', name: 'learning_admin_course_module_')]
#[IsGranted('LEARNING_TRAINING_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::COURSE_MODULES)]
final class DeleteCourseModuleController extends AbstractController
{
    #[Route('/{moduleId}/delete', name: 'delete', requirements: ['trainingId' => '\\d+', 'moduleId' => '\\d+'], methods: ['DELETE'])]
    public function __invoke(Request $request, int $trainingId, int $moduleId): Response
    {
        if (!$this->isCsrfTokenValid('learning_course_module_delete_' . $trainingId . '_' . $moduleId, (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); }
        try { $this->handleCommand(new DeleteCourseModuleCommand($trainingId, $moduleId)); $this->flash()->success('Module supprimé avec ses leçons.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        return $this->redirectToRoute('learning_admin_training_show', ['id' => $trainingId]);
    }
}

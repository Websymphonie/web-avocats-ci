<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\CourseModule;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Command\CourseModule\ReorderCourseModulesCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings/{trainingId}/modules', name: 'learning_admin_course_module_')]
#[IsGranted('LEARNING_TRAINING_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::COURSE_MODULES)]
final class ReorderCourseModulesController extends AbstractController
{
    #[Route('/reorder', name: 'reorder', requirements: ['trainingId' => '\\d+'], methods: ['POST'])]
    public function __invoke(Request $request, int $trainingId): Response
    {
        if (!$this->isCsrfTokenValid('learning_course_module_reorder_' . $trainingId, (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); }
        $ids = array_values(array_map('intval', (array) $request->request->all('moduleIds')));
        try { $this->handleCommand(new ReorderCourseModulesCommand($trainingId, $ids)); $this->flash()->success('Ordre des modules enregistré.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        return $this->redirectToRoute('learning_admin_training_show', ['id' => $trainingId]);
    }
}

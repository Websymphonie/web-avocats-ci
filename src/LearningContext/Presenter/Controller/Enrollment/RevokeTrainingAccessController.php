<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\Enrollment;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\LearningContext\Application\Usecase\Command\RevokeTrainingAccessCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings/{trainingId}/enrollments', name: 'learning_admin_enrollment_')]
#[IsGranted('LEARNING_ENROLLMENT_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::ENROLLMENTS)]
final class RevokeTrainingAccessController extends AbstractController
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[Route('/{enrollmentId}/revoke', name: 'revoke', requirements: ['trainingId' => '\\d+', 'enrollmentId' => '\\d+'], methods: ['POST'])]
    public function __invoke(Request $request, int $trainingId, int $enrollmentId): Response
    {
        if (!$this->isCsrfTokenValid('learning_enrollment_revoke_' . $enrollmentId, (string)$request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
        try {
            $this->handleCommand(new RevokeTrainingAccessCommand($trainingId, $enrollmentId));
            $this->flash()->success('Accès révoqué.');
        } catch (UserFacingError $exception) {
            $this->flash()->errorFromException($exception);
        }
        return $this->redirectToRoute('learning_admin_enrollment_list', ['trainingId' => $trainingId]);
    }
}

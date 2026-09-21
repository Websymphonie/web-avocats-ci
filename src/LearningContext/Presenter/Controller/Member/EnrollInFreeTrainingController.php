<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\Member;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\LearningContext\Application\Usecase\Command\EnrollInFreeTrainingCommand;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/espace/learning/trainings/{uuid}/enroll', name: 'learning_member_training_enroll', methods: ['POST'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class EnrollInFreeTrainingController extends AbstractController
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(Request $request, string $uuid, CurrentUserProvider $currentUser, TrainingRepositoryInterface $trainings): Response
    {
        if (!$this->isCsrfTokenValid('learning_member_training_enroll_' . $uuid, (string)$request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
        if (!Uuid::isValid($uuid)) {
            throw $this->createNotFoundException();
        }
        $user = $currentUser->user();
        if ($user === null || $user->id === null) {
            throw $this->createAccessDeniedException();
        }
        try {
            $training = $trainings->getByUuid($uuid);
            $this->handleCommand(new EnrollInFreeTrainingCommand($training->id, $user->id));
            $this->flash()->success('Vous êtes inscrit à cette formation.');
        } catch (UserFacingError $exception) {
            $this->flash()->errorFromException($exception);
        }
        return $this->redirectToRoute('app_member');
    }
}

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
use Websymphonie\LearningContext\Application\Usecase\Command\StartLessonCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/espace/learning/lessons/{uuid}/start', name: 'learning_member_lesson_start', methods: ['POST'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class StartLessonController extends AbstractController
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(Request $request, string $uuid, CurrentUserProvider $currentUser): Response
    {
        if (!$this->isCsrfTokenValid('learning_member_lesson_start_' . $uuid, (string)$request->request->get('_token'))) {
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
            $this->handleCommand(new StartLessonCommand($uuid, $user->id));
            $this->flash()->success('Leçon commencée.');
        } catch (UserFacingError $exception) {
            $this->flash()->errorFromException($exception);
        }
        return $this->redirectAfterLessonAction($request);
    }

    private function redirectAfterLessonAction(Request $request): Response
    {
        $returnTo = (string) $request->request->get('_return_to', '');
        if (preg_match('#^/espace/formations/[^/?]+(?:/lecons/[^/?]+)?$#', $returnTo) === 1) {
            return $this->redirect($returnTo);
        }

        return $this->redirectToRoute('app_member');
    }
}

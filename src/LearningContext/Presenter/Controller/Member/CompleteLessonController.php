<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\Member;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\LearningContext\Application\Usecase\Command\CompleteLessonCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/espace/learning/lessons/{uuid}/complete', name: 'learning_member_lesson_complete', methods: ['POST'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class CompleteLessonController extends AbstractController
{
    public function __invoke(Request $request, string $uuid, CurrentUserProvider $currentUser): Response
    {
        if (!$this->isCsrfTokenValid('learning_member_lesson_complete_' . $uuid, (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); }
        if (!Uuid::isValid($uuid)) { throw $this->createNotFoundException(); }
        $user = $currentUser->user();
        if ($user === null || $user->id === null) { throw $this->createAccessDeniedException(); }
        try { $this->handleCommand(new CompleteLessonCommand($uuid, $user->id)); $this->flash()->success('Leçon terminée.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        return $this->redirectToRoute('app_member');
    }
}

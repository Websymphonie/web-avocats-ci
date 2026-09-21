<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\Member;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\LearningContext\Application\Usecase\Query\GetAccessibleLiveJoinDetailsQuery;
use Websymphonie\LearningContext\Domain\Exception\InvalidLiveTrainingDetailsException;
use Websymphonie\LearningContext\Domain\Exception\LiveTrainingDetailsNotFoundException;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/espace/learning/trainings/{uuid}/join', name: 'learning_member_live_join', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class JoinLiveTrainingController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(string $uuid, CurrentUserProvider $currentUser): Response
    {
        if (!Uuid::isValid($uuid)) {
            throw $this->createNotFoundException();
        }

        $user = $currentUser->user();
        if ($user === null || $user->id === null) {
            throw $this->createAccessDeniedException();
        }

        try {
            $details = $this->handleQuery(new GetAccessibleLiveJoinDetailsQuery($uuid, $user->id));
        } catch (InvalidLiveTrainingDetailsException $exception) {
            return new Response($exception->getMessage(), Response::HTTP_CONFLICT);
        } catch (LiveTrainingDetailsNotFoundException $exception) {
            throw $this->createNotFoundException($exception->getMessage(), $exception);
        }
        if ($details->joinUrl === null) {
            throw $this->createNotFoundException('Cette session ne propose pas de lien de connexion.');
        }

        return new RedirectResponse($details->joinUrl);
    }
}

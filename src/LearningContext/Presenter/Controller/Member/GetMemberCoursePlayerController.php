<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\Member;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\LearningContext\Application\Usecase\Query\GetMemberCoursePlayerQuery;
use Websymphonie\LearningContext\Domain\Exception\InvalidCourseStructureException;
use Websymphonie\LearningContext\Domain\Exception\LessonNotFoundException;
use Websymphonie\LearningContext\Domain\Exception\TrainingAccessDeniedException;
use Websymphonie\LearningContext\Domain\Exception\TrainingNotFoundException;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/espace/formations')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class GetMemberCoursePlayerController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $mediaUrls)
    {
    }

    #[Route('/{uuid}', name: 'learning_member_course_player', methods: ['GET'])]
    public function __invoke(string $uuid, CurrentUserProvider $currentUser): Response
    {
        return $this->renderPlayer($uuid, $currentUser, null);
    }

    #[Route('/{uuid}/lecons/{lessonUuid}', name: 'learning_member_course_lesson', methods: ['GET'])]
    public function lesson(string $uuid, string $lessonUuid, CurrentUserProvider $currentUser): Response
    {
        return $this->renderPlayer($uuid, $currentUser, $lessonUuid);
    }

    private function renderPlayer(string $uuid, CurrentUserProvider $currentUser, ?string $lessonUuid): Response
    {
        if (!Uuid::isValid($uuid) || ($lessonUuid !== null && !Uuid::isValid($lessonUuid))) {
            throw $this->createNotFoundException();
        }

        $user = $currentUser->user();
        if ($user === null || $user->id === null) {
            throw $this->createAccessDeniedException();
        }

        try {
            $player = $this->handleQuery(new GetMemberCoursePlayerQuery($uuid, $user->id, $lessonUuid));
        } catch (TrainingAccessDeniedException|TrainingNotFoundException|InvalidCourseStructureException|LessonNotFoundException $exception) {
            throw $this->createNotFoundException('Cette formation n’est pas accessible.', $exception);
        }

        $coverUrl = $player->training->coverMediaId !== null
            ? ($this->mediaUrls->resolveMany([$player->training->coverMediaId])[$player->training->coverMediaId] ?? null)
            : null;

        return $this->render('member/trainings/player.html.twig', [
            'title' => $player->training->title,
            'player' => $player,
            'coverUrl' => $coverUrl,
        ]);
    }
}

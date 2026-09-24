<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\Member;

use DateTimeImmutable;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\LearningContext\Application\Usecase\Query\GetMemberLiveSessionQuery;
use Websymphonie\LearningContext\Domain\Exception\LiveTrainingDetailsNotFoundException;
use Websymphonie\LearningContext\Domain\Exception\TrainingAccessDeniedException;
use Websymphonie\LearningContext\Domain\Exception\TrainingNotFoundException;
use Websymphonie\LearningContext\Presenter\Service\YouTubeVideoPresenter;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/espace/formations/{uuid}/live', name: 'learning_member_live_session', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class GetMemberLiveSessionController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $mediaUrls, private readonly YouTubeVideoPresenter $videoPresenter)
    {
    }

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
            $session = $this->handleQuery(new GetMemberLiveSessionQuery($uuid, $user->id));
        } catch (TrainingAccessDeniedException|TrainingNotFoundException|LiveTrainingDetailsNotFoundException $exception) {
            throw $this->createNotFoundException('Cette session Live n’est pas accessible.', $exception);
        }

        $coverUrl = $session->coverMediaId !== null
            ? ($this->mediaUrls->resolveMany([$session->coverMediaId])[$session->coverMediaId] ?? null)
            : null;
        $now = new DateTimeImmutable();
        $liveEmbedUrl = $now >= $session->startsAt && $now < $session->endsAt
            ? $this->videoPresenter->embedUrl($session->liveSource)
            : null;
        $replayEmbedUrl = $now >= $session->endsAt
            ? $this->videoPresenter->embedUrl($session->replaySource)
            : null;

        return $this->render('member/trainings/live.html.twig', [
            'title' => $session->title,
            'session' => $session,
            'coverUrl' => $coverUrl,
            'now' => $now,
            'liveEmbedUrl' => $liveEmbedUrl,
            'replayEmbedUrl' => $replayEmbedUrl,
        ]);
    }
}

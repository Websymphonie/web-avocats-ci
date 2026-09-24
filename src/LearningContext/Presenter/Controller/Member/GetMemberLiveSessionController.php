<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\Member;

use DateTimeImmutable;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\LearningContext\Application\Exception\VideoPlaybackUnavailableException;
use Websymphonie\LearningContext\Application\Service\MuxPlaybackTokenSignerInterface;
use Websymphonie\LearningContext\Application\Usecase\Query\GetMemberLiveSessionQuery;
use Websymphonie\LearningContext\Domain\Enum\VideoProvider;
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
    public function __construct(
        private readonly MediaPublicUrlResolverInterface $mediaUrls,
        private readonly YouTubeVideoPresenter $videoPresenter,
        private readonly MuxPlaybackTokenSignerInterface $muxSigner,
        private readonly LoggerInterface $logger,
    ) {}

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
        $isOngoing = $now >= $session->startsAt && $now < $session->endsAt;
        $activeSource = $isOngoing
            ? $session->liveSource
            : ($now >= $session->endsAt ? $session->replaySource : null);
        $muxPlaybackId = $activeSource?->provider === VideoProvider::MUX ? $activeSource->externalId : null;
        $muxPlaybackToken = null;
        $videoUnavailable = false;

        if ($muxPlaybackId !== null) {
            try {
                $muxPlaybackToken = $this->muxSigner->signPlayback($muxPlaybackId);
            } catch (VideoPlaybackUnavailableException $exception) {
                $this->logger->error('Unable to prepare authorized Mux LIVE playback.', [
                    'training_uuid' => $session->trainingUuid,
                    'source' => $isOngoing ? 'live' : 'replay',
                    'error_type' => $exception::class,
                ]);
                $videoUnavailable = true;
            }
        }

        $liveEmbedUrl = $isOngoing ? $this->videoPresenter->embedUrl($session->liveSource) : null;
        $replayEmbedUrl = !$isOngoing && $now >= $session->endsAt
            ? $this->videoPresenter->embedUrl($session->replaySource)
            : null;

        $response = $this->render('member/trainings/live.html.twig', [
            'title' => $session->title,
            'session' => $session,
            'coverUrl' => $coverUrl,
            'now' => $now,
            'liveEmbedUrl' => $liveEmbedUrl,
            'replayEmbedUrl' => $replayEmbedUrl,
            'hasReplaySource' => $session->replaySource !== null
                && in_array($session->replaySource->provider, [VideoProvider::YOUTUBE, VideoProvider::MUX], true),
            'muxPlaybackId' => $muxPlaybackId,
            'muxPlaybackToken' => $muxPlaybackToken,
            'videoUnavailable' => $videoUnavailable,
        ]);

        if ($muxPlaybackToken !== null) {
            $response->headers->set('Cache-Control', 'private, no-store, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }
}

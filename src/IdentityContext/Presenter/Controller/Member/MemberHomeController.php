<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Controller\Member;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\LearningContext\Application\Usecase\Query\GetMemberTrainingSummariesQuery;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

final class MemberHomeController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $mediaUrls)
    {
    }

    #[Route(path: '/espace', name: 'app_member', methods: ['GET'])]
    public function __invoke(CurrentUserProvider $currentUser): Response
    {
        $user = $currentUser->user();
        if ($user === null || $user->id === null) {
            throw $this->createAccessDeniedException();
        }

        $now = new DateTimeImmutable();
        $upcomingLives = array_values(array_filter(
            $this->handleQuery(new GetMemberTrainingSummariesQuery($user->id)),
            static fn ($summary): bool => $summary->training->type === TrainingType::LIVE
                && $summary->training->liveDetails !== null
                && $summary->training->liveDetails->startsAt > $now,
        ));
        usort(
            $upcomingLives,
            static fn ($left, $right): int => $left->training->liveDetails->startsAt <=> $right->training->liveDetails->startsAt,
        );
        $upcomingLives = array_slice($upcomingLives, 0, 3);
        $mediaIds = array_values(array_filter(array_map(
            static fn ($summary): ?int => $summary->training->coverMediaId,
            $upcomingLives,
        )));

        return $this->render('member/home/index.html.twig', [
            'title' => 'Tableau de bord',
            'upcomingLives' => $upcomingLives,
            'liveCoverUrls' => $this->mediaUrls->resolveMany($mediaIds),
        ]);
    }
}

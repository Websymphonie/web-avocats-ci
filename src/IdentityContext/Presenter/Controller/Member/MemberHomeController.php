<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Controller\Member;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\LearningContext\Application\Usecase\Query\GetMemberTrainingSummariesQuery;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Model\MemberTrainingSummary;
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
        $summaries = $this->handleQuery(new GetMemberTrainingSummariesQuery($user->id));
        $upcomingLives = array_values(array_filter(
            $summaries,
            static fn ($summary): bool => $summary->training->type === TrainingType::LIVE
                && $summary->training->liveDetails !== null
                && $summary->training->liveDetails->startsAt > $now,
        ));
        usort(
            $upcomingLives,
            static fn ($left, $right): int => $left->training->liveDetails->startsAt <=> $right->training->liveDetails->startsAt,
        );
        $upcomingLives = array_slice($upcomingLives, 0, 3);
        $inProgressCourses = array_values(array_filter(
            $summaries,
            static fn (MemberTrainingSummary $summary): bool => $summary->training->type === TrainingType::COURSE
                && $summary->progress !== null
                && $summary->progress->startedLessons > 0
                && $summary->progress->progressPercentage < 100,
        ));
        $notStartedCourses = array_values(array_filter(
            $summaries,
            static fn (MemberTrainingSummary $summary): bool => $summary->training->type === TrainingType::COURSE
                && $summary->progress !== null
                && $summary->progress->progressPercentage === 0,
        ));
        $continuationCourse = $inProgressCourses[0] ?? $notStartedCourses[0] ?? null;

        $mediaIds = [];
        foreach (array_merge($upcomingLives, $continuationCourse !== null ? [$continuationCourse] : []) as $summary) {
            if ($summary->training->coverMediaId !== null) {
                $mediaIds[] = $summary->training->coverMediaId;
            }
        }
        $coverUrls = $this->mediaUrls->resolveMany(array_values(array_unique($mediaIds)));

        return $this->render('member/home/index.html.twig', [
            'title' => 'Tableau de bord',
            'upcomingLives' => $upcomingLives,
            'liveCoverUrls' => $coverUrls,
            'continuationCourse' => $continuationCourse,
            'continuationCoverUrl' => $continuationCourse !== null && $continuationCourse->training->coverMediaId !== null
                ? ($coverUrls[$continuationCourse->training->coverMediaId] ?? null)
                : null,
        ]);
    }
}

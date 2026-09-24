<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\Member;

use DateTimeImmutable;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\LearningContext\Application\Usecase\Query\GetMemberTrainingSummariesQuery;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/espace/formations', name: 'learning_member_training_list', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class GetMemberTrainingListController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $mediaUrls)
    {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(Request $request, CurrentUserProvider $currentUser): Response
    {
        $user = $currentUser->user();
        if ($user === null || $user->id === null) {
            throw $this->createAccessDeniedException();
        }

        $summaries = $this->handleQuery(new GetMemberTrainingSummariesQuery($user->id));
        $now = new DateTimeImmutable();
        $groups = [
            'current' => [],
            'upcoming' => [],
            'completed' => [],
        ];
        foreach ($summaries as $summary) {
            if ($summary->training->type === TrainingType::COURSE) {
                if ($summary->progress?->progressPercentage >= 100) {
                    $groups['completed'][] = $summary;
                } else {
                    $groups['current'][] = $summary;
                }
                continue;
            }

            $startsAt = $summary->training->liveDetails?->startsAt;
            $endsAt = $summary->training->liveDetails?->endsAt;
            if ($startsAt !== null && $startsAt > $now) {
                $groups['upcoming'][] = $summary;
            } elseif ($endsAt !== null && $endsAt <= $now) {
                $groups['completed'][] = $summary;
            } else {
                $groups['current'][] = $summary;
            }
        }

        $view = $request->query->getString('view', 'all');
        if (!in_array($view, ['all', 'current', 'upcoming', 'completed'], true)) {
            $view = 'all';
        }
        $visibleSummaries = $view === 'all' ? $summaries : $groups[$view];
        $mediaIds = array_values(array_filter(array_map(static fn($summary): ?int => $summary->training->coverMediaId, $summaries)));

        return $this->render('member/trainings/index.html.twig', [
            'title' => 'Mes formations',
            'summaries' => $visibleSummaries,
            'allSummaries' => $summaries,
            'groups' => $groups,
            'view' => $view,
            'now' => $now,
            'coverUrls' => $this->mediaUrls->resolveMany($mediaIds),
        ]);
    }
}

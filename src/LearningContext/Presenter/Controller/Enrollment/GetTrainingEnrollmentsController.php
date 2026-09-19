<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\Enrollment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Application\Service\User\UserDirectoryInterface;
use Websymphonie\LearningContext\Application\Usecase\Query\GetTrainingEnrollmentsQuery;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentSource;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentStatus;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings/{trainingId}/enrollments', name: 'learning_admin_enrollment_')]
#[IsGranted('LEARNING_ENROLLMENT_VIEW')]
final class GetTrainingEnrollmentsController extends AbstractController
{
    #[Route('', name: 'list', requirements: ['trainingId' => '\\d+'], methods: ['GET'])]
    public function __invoke(Request $request, int $trainingId, TrainingRepositoryInterface $trainings, UserDirectoryInterface $users): Response
    {
        return $this->render('learning/admin/enrollment/index.html.twig', [
            'training' => $trainings->getById($trainingId),
            'view' => $this->handleQuery(new GetTrainingEnrollmentsQuery($trainingId, EnrollmentStatus::tryFrom((string) $request->query->get('status')), EnrollmentSource::tryFrom((string) $request->query->get('source')), (string) $request->query->get('search', ''), max(1, (int) $request->query->get('page', 1)))),
            'users' => $users->search('', 200),
            'statusOptions' => EnrollmentStatus::cases(),
            'sourceOptions' => EnrollmentSource::cases(),
        ]);
    }
}

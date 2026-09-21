<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\Training;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\LearningContext\Application\Usecase\Command\CreateTrainingCommand;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Presenter\Form\Training\TrainingFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings', name: 'learning_admin_training_')]
#[IsGranted('LEARNING_TRAINING_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::TRAININGS)]
final class CreateTrainingController extends AbstractController
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    #[Route('/new/live', name: 'new_live', defaults: ['type' => 'LIVE'], methods: ['GET', 'POST'])]
    public function __invoke(Request $request, string $type = 'COURSE'): Response
    {
        $trainingType = TrainingType::tryFrom(strtoupper($type)) ?? TrainingType::COURSE;
        $command = new CreateTrainingCommand(type: $trainingType);
        $form = $this->createForm(TrainingFormType::class, $command);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handleCommand($command);
                $this->flash()->success('Formation créée en brouillon.');
                return $this->redirectToRoute('learning_admin_training_list');
            } catch (UserFacingError $exception) {
                $this->flash()->errorFromException($exception);
            }
        }

        return $this->render('learning/admin/training/create.html.twig', ['form' => $form->createView(), 'trainingType' => $trainingType]);
    }
}

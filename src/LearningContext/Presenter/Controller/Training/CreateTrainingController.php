<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\Training;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\LearningContext\Application\Usecase\Command\CreateTrainingCommand;
use Websymphonie\LearningContext\Presenter\Form\Training\TrainingFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings', name: 'learning_admin_training_')]
#[IsGranted('LEARNING_TRAINING_MANAGE')]
final class CreateTrainingController extends AbstractController
{
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $command = new CreateTrainingCommand();
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

        return $this->render('learning/admin/training/create.html.twig', ['form' => $form->createView()]);
    }
}

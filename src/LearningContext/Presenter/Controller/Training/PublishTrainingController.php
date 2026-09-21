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
use Websymphonie\LearningContext\Application\Usecase\Command\PublishTrainingCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings', name: 'learning_admin_training_')]
#[IsGranted('LEARNING_TRAINING_PUBLISH')]
#[HasGroupAccess(RoleGroupEnum::TRAININGS)]
final class PublishTrainingController extends AbstractController
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[Route('/{id}/publish', name: 'publish', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function __invoke(Request $request, int $id): Response
    {
        if (!$this->isCsrfTokenValid('training_publish_' . $id, (string)$request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
        try {
            $this->handleCommand(new PublishTrainingCommand($id));
            $this->flash()->success('Formation publiée.');
        } catch (UserFacingError $exception) {
            $this->flash()->errorFromException($exception);
        }

        return $this->redirectToRoute('learning_admin_training_list');
    }
}

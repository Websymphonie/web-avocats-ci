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
use Websymphonie\LearningContext\Application\Usecase\Command\DeleteTrainingCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/trainings', name: 'learning_admin_training_')]
#[IsGranted('LEARNING_TRAINING_DELETE')]
#[HasGroupAccess(RoleGroupEnum::TRAININGS)]
final class DeleteTrainingController extends AbstractController
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\\d+'], methods: ['DELETE'])]
    public function __invoke(Request $request, int $id): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $id, (string)$request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
        try {
            $this->handleCommand(new DeleteTrainingCommand($id));
            $this->flash()->success('Formation supprimée.');
        } catch (UserFacingError $exception) {
            $this->flash()->errorFromException($exception);
        }

        return $this->redirectToRoute('learning_admin_training_list');
    }
}

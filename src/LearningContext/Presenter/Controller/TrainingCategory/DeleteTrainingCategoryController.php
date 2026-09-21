<?php
declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Controller\TrainingCategory;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\LearningContext\Application\Usecase\Command\TrainingCategory\DeleteTrainingCategoryCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/categories', name: 'learning_admin_category_')]
#[IsGranted('LEARNING_CATEGORY_DELETE')]
#[HasGroupAccess(RoleGroupEnum::CATEGORY_TRAININGS)]
final class DeleteTrainingCategoryController extends AbstractController
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
            $this->handleCommand(new DeleteTrainingCategoryCommand($id));
            $this->flash()->success('Catégorie de formation supprimée.');
        } catch (UserFacingError $exception) {
            $this->flash()->errorFromException($exception);
        }
        return $this->redirectToRoute('learning_admin_category_list');
    }
}

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
use Websymphonie\LearningContext\Application\Usecase\Command\TrainingCategory\CreateTrainingCategoryCommand;
use Websymphonie\LearningContext\Presenter\Form\Taxonomy\TrainingCategoryFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/categories', name: 'learning_admin_category_')]
#[IsGranted('LEARNING_CATEGORY_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::CATEGORY_TRAININGS)]
final class CreateTrainingCategoryController extends AbstractController
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $command = new CreateTrainingCategoryCommand();
        $form = $this->createForm(TrainingCategoryFormType::class, $command);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handleCommand($command);
                $this->flash()->success('Catégorie de formation créée.');
                return $this->redirectToRoute('learning_admin_category_list');
            } catch (UserFacingError $exception) {
                $this->flash()->errorFromException($exception);
            }
        }
        return $this->render('learning/admin/taxonomy/form.html.twig', ['form' => $form->createView(), 'title' => 'Nouvelle catégorie de formation', 'backRoute' => 'learning_admin_category_list']);
    }
}

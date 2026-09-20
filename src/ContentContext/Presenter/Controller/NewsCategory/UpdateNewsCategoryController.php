<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Controller\NewsCategory;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\NewsCategory\UpdateNewsCategoryCommand;
use Websymphonie\ContentContext\Domain\Repository\NewsCategoryRepositoryInterface;
use Websymphonie\ContentContext\Presenter\Form\NewsCategory\NewsCategoryFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/news-categories', name: 'content_admin_news_category_')]
#[IsGranted('CONTENT_NEWS_CATEGORY_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::CATEGORY_NEWS)]
final class UpdateNewsCategoryController extends AbstractController
{
    public function __construct(private readonly NewsCategoryRepositoryInterface $repository) {}
    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function __invoke(Request $request, int $id): Response
    {
        $category = $this->repository->getById($id); $command = new UpdateNewsCategoryCommand($category->id, $category->name, $category->description); $form = $this->createForm(NewsCategoryFormType::class, $command); $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { try { $this->handleCommand($command); $this->flash()->success('Catégorie modifiée.'); return $this->redirectToRoute('content_admin_news_category_list'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } }
        return $this->render('content/admin/news_category/form.html.twig', ['form' => $form->createView(), 'title' => 'Modifier la catégorie']);
    }
}

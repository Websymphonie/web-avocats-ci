<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Controller\Tag;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\Tag\UpdateTagCommand;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;
use Websymphonie\ContentContext\Presenter\Form\Tag\TagFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/tags', name: 'content_admin_tag_')]
#[IsGranted('CONTENT_TAG_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::TAGS)]
final class UpdateTagController extends AbstractController
{
    public function __construct(private readonly TagRepositoryInterface $repository) {}
    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function __invoke(Request $request, int $id): Response
    {
        $tag = $this->repository->getById($id); $command = new UpdateTagCommand($tag->id, $tag->name); $form = $this->createForm(TagFormType::class, $command); $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { try { $this->handleCommand($command); $this->flash()->success('Tag modifié.'); return $this->redirectToRoute('content_admin_tag_list'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } }
        return $this->render('content/admin/tag/form.html.twig', ['form' => $form->createView(), 'title' => 'Modifier le tag']);
    }
}

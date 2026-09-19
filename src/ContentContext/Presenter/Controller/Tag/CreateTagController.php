<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Controller\Tag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\Tag\CreateTagCommand;
use Websymphonie\ContentContext\Presenter\Form\Tag\TagFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/tags', name: 'content_admin_tag_')]
#[IsGranted('CONTENT_TAG_MANAGE')]
final class CreateTagController extends AbstractController
{
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $command = new CreateTagCommand(); $form = $this->createForm(TagFormType::class, $command); $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { try { $this->handleCommand($command); $this->flash()->success('Tag créé.'); return $this->redirectToRoute('content_admin_tag_list'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } }
        return $this->render('content/admin/tag/form.html.twig', ['form' => $form->createView(), 'title' => 'Nouveau tag']);
    }
}

<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Controller\EditorialVideo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo\CreateEditorialVideoCommand;
use Websymphonie\ContentContext\Presenter\Form\EditorialVideo\EditorialVideoFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/videos', name: 'content_admin_video_')]
#[IsGranted('CONTENT_VIDEO_MANAGE')]
final class CreateEditorialVideoController extends AbstractController { #[Route('/new', name: 'new', methods: ['GET', 'POST'])] public function __invoke(Request $request): Response { $command = new CreateEditorialVideoCommand(); $form = $this->createForm(EditorialVideoFormType::class, $command); $form->handleRequest($request); if ($form->isSubmitted() && $form->isValid()) { try { $this->handleCommand($command); $this->flash()->success('Vidéo éditoriale créée en brouillon.'); return $this->redirectToRoute('content_admin_video_list'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } } return $this->render('content/admin/editorial_video/create.html.twig', ['form' => $form->createView()]); } }

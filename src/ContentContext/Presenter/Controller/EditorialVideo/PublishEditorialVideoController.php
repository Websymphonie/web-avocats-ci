<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Controller\EditorialVideo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo\PublishEditorialVideoCommand;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/videos', name: 'content_admin_video_')]
#[IsGranted('CONTENT_VIDEO_PUBLISH')]
final class PublishEditorialVideoController extends AbstractController { #[Route('/{id}/publish', name: 'publish', requirements: ['id' => '\\d+'], methods: ['POST'])] public function __invoke(Request $request, int $id): Response { if (!$this->isCsrfTokenValid('video_publish_' . $id, (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); } try { $this->handleCommand(new PublishEditorialVideoCommand($id)); $this->flash()->success('Vidéo éditoriale publiée.'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } return $this->redirectToRoute('content_admin_video_list'); } }

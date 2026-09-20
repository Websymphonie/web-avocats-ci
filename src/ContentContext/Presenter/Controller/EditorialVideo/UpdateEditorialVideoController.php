<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Controller\EditorialVideo;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo\UpdateEditorialVideoCommand;
use Websymphonie\ContentContext\Application\Usecase\Query\EditorialVideo\GetEditorialVideoDetailsQuery;
use Websymphonie\ContentContext\Presenter\Form\EditorialVideo\EditorialVideoFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/videos', name: 'content_admin_video_')]
#[IsGranted('CONTENT_VIDEO_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::VIDEOS)]
final class UpdateEditorialVideoController extends AbstractController { #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])] public function __invoke(Request $request, int $id): Response { $video = $this->handleQuery(new GetEditorialVideoDetailsQuery($id)); $command = new UpdateEditorialVideoCommand($video->id, $video->title, $video->excerpt, $video->description, $video->provider, $video->videoUrl, array_map(static fn ($tag): int => $tag->id, $video->tags)); $form = $this->createForm(EditorialVideoFormType::class, $command); $form->handleRequest($request); if ($form->isSubmitted() && $form->isValid()) { try { $this->handleCommand($command); $this->flash()->success('Vidéo éditoriale modifiée.'); return $this->redirectToRoute('content_admin_video_list'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); } } return $this->render('content/admin/editorial_video/edit.html.twig', ['video' => $video, 'form' => $form->createView()]); } }

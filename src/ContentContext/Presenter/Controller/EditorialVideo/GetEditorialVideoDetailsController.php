<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Presenter\Controller\EditorialVideo;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Query\EditorialVideo\GetEditorialVideoDetailsQuery;
use Websymphonie\SharedContext\Presenter\AbstractController;
#[Route('/videos', name: 'content_admin_video_')]
#[IsGranted('CONTENT_VIDEO_VIEW')]
#[HasGroupAccess(RoleGroupEnum::VIDEOS)]
final class GetEditorialVideoDetailsController extends AbstractController { public function __construct(private readonly RichTextSanitizerInterface $sanitizer) {} #[Route('/{id}', name: 'show', requirements: ['id' => '\\d+'], methods: ['GET'])] public function __invoke(int $id): Response { $video = $this->handleQuery(new GetEditorialVideoDetailsQuery($id)); return $this->render('content/admin/editorial_video/show.html.twig', ['video' => $video, 'safeDescription' => $this->sanitizer->sanitize($video->description), 'embedUrl' => $video->youtubeEmbedUrl()]); } }

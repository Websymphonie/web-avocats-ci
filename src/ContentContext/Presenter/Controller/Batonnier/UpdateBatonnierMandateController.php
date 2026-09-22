<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\Batonnier;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\Batonnier\UpdateBatonnierMandateCommand;
use Websymphonie\ContentContext\Application\Usecase\Query\Batonnier\GetBatonnierMandateQuery;
use Websymphonie\ContentContext\Presenter\Form\Batonnier\BatonnierMandateFormType;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/batonnier', name: 'content_admin_batonnier_')]
#[IsGranted('BATONNIER_EDIT')]
#[HasGroupAccess(RoleGroupEnum::BATONNIER)]
final class UpdateBatonnierMandateController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $mediaUrls) {}

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function __invoke(Request $request, int $id): Response
    {
        $mandate = $this->handleQuery(new GetBatonnierMandateQuery($id));
        $command = new UpdateBatonnierMandateCommand($mandate->id, $mandate->fullName, mandateStartedAt: $mandate->mandateStartedAt, mandateEndedAt: $mandate->mandateEndedAt, summary: $mandate->summary);
        $form = $this->createForm(BatonnierMandateFormType::class, $command);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handleCommand($command);
                $this->flash()->success('Mandat du Bâtonnier modifié.');
                return $this->redirectToRoute('content_admin_batonnier_edit', ['id' => $id]);
            } catch (UserFacingError $exception) {
                $this->flash()->errorFromException($exception);
            }
        }
        $portraitUrls = $mandate->portraitMediaId !== null ? $this->mediaUrls->resolveMany([$mandate->portraitMediaId]) : [];

        return $this->render('content/admin/batonnier/edit.html.twig', ['mandate' => $mandate, 'form' => $form->createView(), 'portraitUrl' => $portraitUrls[$mandate->portraitMediaId] ?? null]);
    }
}

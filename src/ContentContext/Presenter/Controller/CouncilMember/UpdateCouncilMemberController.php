<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\CouncilMember;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\CouncilMember\UpdateCouncilMemberCommand;
use Websymphonie\ContentContext\Application\Usecase\Query\CouncilMember\GetCouncilMemberQuery;
use Websymphonie\ContentContext\Presenter\Form\CouncilMember\CouncilMemberFormType;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/conseil-ordre', name: 'content_admin_council_member_')]
#[IsGranted('COUNCIL_MEMBER_EDIT')]
#[HasGroupAccess(RoleGroupEnum::COUNCIL_MEMBERS)]
final class UpdateCouncilMemberController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $mediaUrls)
    {
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function __invoke(Request $request, int $id): Response
    {
        $member = $this->handleQuery(new GetCouncilMemberQuery($id));
        $command = new UpdateCouncilMemberCommand($member->id, $member->fullName, $member->function, sortOrder: $member->sortOrder, mandateStartedAt: $member->mandateStartedAt, mandateEndedAt: $member->mandateEndedAt);
        $form = $this->createForm(CouncilMemberFormType::class, $command);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handleCommand($command);
                $this->flash()->success('Membre du Conseil de l’Ordre modifié.');

                return $this->redirectToRoute('content_admin_council_member_edit', ['id' => $id]);
            } catch (UserFacingError $exception) {
                $this->flash()->errorFromException($exception);
            }
        }
        $portraitUrls = $member->portraitMediaId !== null ? $this->mediaUrls->resolveMany([$member->portraitMediaId]) : [];

        return $this->render('content/admin/council_member/edit.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
            'portraitUrl' => $portraitUrls[$member->portraitMediaId] ?? null,
        ]);
    }
}

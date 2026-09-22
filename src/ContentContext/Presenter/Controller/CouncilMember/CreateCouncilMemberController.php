<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\CouncilMember;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\CouncilMember\CreateCouncilMemberCommand;
use Websymphonie\ContentContext\Presenter\Form\CouncilMember\CouncilMemberFormType;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/conseil-ordre', name: 'content_admin_council_member_')]
#[IsGranted('COUNCIL_MEMBER_CREATE')]
#[HasGroupAccess(RoleGroupEnum::COUNCIL_MEMBERS)]
final class CreateCouncilMemberController extends AbstractController
{
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $command = new CreateCouncilMemberCommand();
        $form = $this->createForm(CouncilMemberFormType::class, $command);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $member = $this->handleCommand($command);
                $this->flash()->success('Membre du Conseil de l’Ordre enregistré.');

                return $this->redirectToRoute('content_admin_council_member_show', ['id' => $member->id]);
            } catch (UserFacingError $exception) {
                $this->flash()->errorFromException($exception);
            }
        }

        return $this->render('content/admin/council_member/create.html.twig', ['form' => $form->createView()]);
    }
}

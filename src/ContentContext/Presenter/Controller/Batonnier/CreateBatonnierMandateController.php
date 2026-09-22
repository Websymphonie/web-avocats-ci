<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\Batonnier;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Command\Batonnier\CreateBatonnierMandateCommand;
use Websymphonie\ContentContext\Presenter\Form\Batonnier\BatonnierMandateFormType;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/batonnier', name: 'content_admin_batonnier_')]
#[IsGranted('BATONNIER_CREATE')]
#[HasGroupAccess(RoleGroupEnum::BATONNIER)]
final class CreateBatonnierMandateController extends AbstractController
{
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $command = new CreateBatonnierMandateCommand();
        $form = $this->createForm(BatonnierMandateFormType::class, $command);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $mandate = $this->handleCommand($command);
                $this->flash()->success('Mandat du Bâtonnier enregistré.');
                return $this->redirectToRoute('content_admin_batonnier_show', ['id' => $mandate->id]);
            } catch (UserFacingError $exception) {
                $this->flash()->errorFromException($exception);
            }
        }

        return $this->render('content/admin/batonnier/create.html.twig', ['form' => $form->createView()]);
    }
}

<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Presenter\Controller;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\PaymentContext\Application\Usecase\Command\SaveTrainingOfferCommand;
use Websymphonie\PaymentContext\Domain\Repository\TrainingOfferRepositoryInterface;
use Websymphonie\PaymentContext\Presenter\Form\TrainingOfferFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/offers', name: 'payment_admin_offer_')]
#[IsGranted('PAYMENT_OFFER_MANAGE')]
#[HasGroupAccess(RoleGroupEnum::PAYMENT_OFFERS)]
final class SaveTrainingOfferController extends AbstractController
{
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->formResponse($request, new SaveTrainingOfferCommand(), false);
    }

    #[Route('/{trainingId}/edit', name: 'edit', requirements: ['trainingId' => '\\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, int $trainingId, TrainingOfferRepositoryInterface $offers): Response
    {
        $offer = $offers->findByTrainingId($trainingId);
        if ($offer === null) { throw $this->createNotFoundException(); }
        return $this->formResponse($request, new SaveTrainingOfferCommand($offer->trainingId, $offer->amount, $offer->currency, $offer->active), true);
    }

    private function formResponse(Request $request, SaveTrainingOfferCommand $command, bool $locked): Response
    {
        $form = $this->createForm(TrainingOfferFormType::class, $command, ['training_locked' => $locked]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try { $this->handleCommand($command); $this->flash()->success('Tarif enregistré.'); return $this->redirectToRoute('payment_admin_offer_list'); } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        }
        return $this->render('payment/admin/offer/form.html.twig', ['form' => $form->createView(), 'isEdit' => $locked]);
    }
}

<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Presenter\Controller\Member;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\PaymentContext\Application\Usecase\Command\InitiateTrainingPaymentCommand;
use Websymphonie\PaymentContext\Application\Service\TrainingCatalogInterface;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/espace/payments/trainings/{uuid}/initiate', name: 'payment_member_training_initiate', methods: ['POST'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class InitiateTrainingPaymentController extends AbstractController
{
    public function __invoke(Request $request, string $uuid, CurrentUserProvider $currentUser, TrainingCatalogInterface $trainings): Response
    {
        if (!$this->isCsrfTokenValid('payment_initiate_' . $uuid, (string) $request->request->get('_token'))) { throw $this->createAccessDeniedException('Jeton CSRF invalide.'); }
        if (!Uuid::isValid($uuid)) { throw $this->createNotFoundException(); }
        $user = $currentUser->user();
        if ($user === null || $user->id === null) { throw $this->createAccessDeniedException(); }
        try {
            $training = $trainings->getByUuid($uuid);
            $payment = $this->handleCommand(new InitiateTrainingPaymentCommand($user->id, $training->id, (string) $request->request->get('idempotencyKey')));
            $this->flash()->success('Paiement initialisé. La confirmation sera traitée par le fournisseur de paiement.');
            return $this->redirectToRoute('payment_member_status', ['paymentUuid' => $payment->uuid]);
        } catch (UserFacingError $exception) { $this->flash()->errorFromException($exception); }
        return $this->redirectToRoute('app_member');
    }
}

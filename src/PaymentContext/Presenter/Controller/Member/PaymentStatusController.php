<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Presenter\Controller\Member;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\PaymentContext\Application\Usecase\Query\GetMemberPaymentStatusQuery;
use Websymphonie\PaymentContext\Domain\Exception\PaymentNotFoundException;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/espace/payments/{paymentUuid}/status', name: 'payment_member_status', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class PaymentStatusController extends AbstractController
{
    public function __invoke(
        string $paymentUuid,
        CurrentUserProvider $currentUser,
        #[Autowire('%env(KKIAPAY_PUBLIC_KEY)%')]
        string $kkiapayPublicKey,
        #[Autowire('%env(bool:KKIAPAY_SANDBOX)%')]
        bool $kkiapaySandbox,
    ): Response {
        if (!Uuid::isValid($paymentUuid)) {
            throw $this->createNotFoundException();
        }
        $user = $currentUser->user();
        if ($user === null || $user->id === null) {
            throw $this->createAccessDeniedException();
        }

        try {
            $payment = $this->handleQuery(new GetMemberPaymentStatusQuery($paymentUuid));
        } catch (PaymentNotFoundException $exception) {
            throw $this->createNotFoundException($exception->getMessage(), $exception);
        }
        if ($payment->userId !== $user->id) {
            throw $this->createNotFoundException();
        }

        return $this->render('payment/member/status.html.twig', [
            'payment' => $payment,
            'kkiapayPublicKey' => $kkiapayPublicKey,
            'kkiapaySandbox' => $kkiapaySandbox,
        ]);
    }
}

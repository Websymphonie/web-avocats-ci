<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Presenter\Controller;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\PaymentContext\Application\Usecase\Query\GetPaymentDetailsQuery;
use Websymphonie\PaymentContext\Domain\Exception\PaymentNotFoundException;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/payments', name: 'payment_admin_payment_')]
#[IsGranted('PAYMENT_VIEW')]
#[HasGroupAccess(RoleGroupEnum::PAYMENTS)]
final class GetPaymentDetailsController extends AbstractController
{
    #[Route('/{uuid}', name: 'show', methods: ['GET'])]
    public function __invoke(string $uuid): Response
    {
        try { $item = $this->handleQuery(new GetPaymentDetailsQuery($uuid)); } catch (PaymentNotFoundException $exception) { throw $this->createNotFoundException($exception->getMessage(), $exception); }
        return $this->render('payment/admin/payment/show.html.twig', [
            'payment' => $item->payment,
            'training' => $item->training,
            'user' => $item->user,
        ]);
    }
}

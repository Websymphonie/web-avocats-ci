<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Presenter\Controller;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\PaymentContext\Application\Usecase\Query\GetPaymentListQuery;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/payments', name: 'payment_admin_payment_')]
#[IsGranted('PAYMENT_VIEW')]
#[HasGroupAccess(RoleGroupEnum::PAYMENTS)]
final class GetPaymentListController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        return $this->render('payment/admin/payment/index.html.twig', ['items' => $this->handleQuery(new GetPaymentListQuery(max(1, $request->query->getInt('page', 1))))]);
    }
}

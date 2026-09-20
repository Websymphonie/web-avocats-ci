<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Presenter\Controller;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\PaymentContext\Application\Usecase\Query\GetTrainingOfferListQuery;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/offers', name: 'payment_admin_offer_')]
#[IsGranted('PAYMENT_OFFER_VIEW')]
#[HasGroupAccess(RoleGroupEnum::PAYMENT_OFFERS)]
final class GetTrainingOfferListController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        return $this->render('payment/admin/offer/index.html.twig', [
            'items' => $this->handleQuery(new GetTrainingOfferListQuery(max(1, $request->query->getInt('page', 1)))),
        ]);
    }
}

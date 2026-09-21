<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Presenter\Controller\Member;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\PaymentContext\Application\Usecase\Query\GetMemberPaymentsQuery;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/espace/paiements', name: 'payment_member_list', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class GetMemberPaymentsController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(Request $request, CurrentUserProvider $currentUser): Response
    {
        $user = $currentUser->user();
        if ($user === null || $user->id === null) {
            throw $this->createAccessDeniedException();
        }

        $payments = $this->handleQuery(new GetMemberPaymentsQuery(
            userId: $user->id,
            page: max(1, $request->query->getInt('page', 1)),
        ));

        return $this->render('payment/member/index.html.twig', [
            'title' => 'Mes paiements',
            'payments' => $payments,
        ]);
    }
}

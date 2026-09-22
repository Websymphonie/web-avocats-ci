<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Presenter\Controller\Backoffice;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContactContext\Application\Usecase\Command\RetryContactMessageDeliveryCommand;
use Websymphonie\ContactContext\Domain\Enum\ContactMessageDeliveryStatus;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/admin/contact/messages', name: 'contact_admin_message_')]
#[IsGranted('CONTACT_MESSAGE_RETRY')]
#[HasGroupAccess(RoleGroupEnum::CONTACT_MESSAGES)]
final class RetryContactMessageDeliveryController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/{uuid}/retry', name: 'retry', requirements: ['uuid' => '[0-9a-fA-F-]{36}'], methods: ['POST'])]
    public function __invoke(Request $request, string $uuid): Response
    {
        if (!$this->isCsrfTokenValid('contact_message_retry_' . $uuid, $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        try {
            $message = $this->handleCommand(new RetryContactMessageDeliveryCommand($uuid));
            if ($message->deliveryStatus === ContactMessageDeliveryStatus::SENT) {
                $this->flash()->success('Le message a été envoyé.');
            } else {
                $this->flash()->danger('L’envoi a échoué. Le message reste enregistré et pourra être relancé.');
            }
        } catch (UserFacingError $exception) {
            $this->flash()->errorFromException($exception);
        }

        return $this->redirectToRoute('contact_admin_message_show', ['uuid' => $uuid]);
    }
}

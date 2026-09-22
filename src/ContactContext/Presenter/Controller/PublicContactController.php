<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Presenter\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\ContactContext\Application\Service\PublicContactSettingsProvider;
use Websymphonie\ContactContext\Application\Usecase\Command\SubmitContactMessageCommand;
use Websymphonie\ContactContext\Application\Usecase\CommandHandler\ContactRateLimitExceeded;
use Websymphonie\ContactContext\Presenter\Form\ContactMessageType;
use Websymphonie\SharedContext\Presenter\AbstractController;

final class PublicContactController extends AbstractController
{
    public function __construct(private readonly PublicContactSettingsProvider $settingsProvider)
    {
    }

    #[Route('/contact', name: 'web_contact', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $command = new SubmitContactMessageCommand();
        $form = $this->createForm(ContactMessageType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command->ip = $request->getClientIp();
            try {
                $message = $this->handleCommand($command);
                if ($message->deliveryStatus->value === 'SENT') {
                    $this->flash()->success('Votre message a bien été envoyé.');
                } else {
                    $this->flash()->danger('Votre message a été enregistré, mais son envoi a échoué. Notre équipe pourra le traiter ultérieurement.');
                }

                return $this->redirectToRoute('web_contact');
            } catch (ContactRateLimitExceeded) {
                $this->flash()->danger('Trop de demandes ont été envoyées depuis cette adresse. Veuillez réessayer plus tard.');
            }
        }

        return $this->render('web/contact/index.html.twig', [
            'form' => $form->createView(),
            'contactSettings' => $this->settingsProvider->get(),
        ]);
    }
}

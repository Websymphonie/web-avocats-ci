<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Presenter\Controller\Reset;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\AuthContext\Application\Usecase\Command\ResetPassword\EmailRequestCommand;
use Websymphonie\AuthContext\Presenter\Form\ResetPassword\EmailRequestType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\AuthContext\Application\Service\ResetPasswordThrottle;
use Websymphonie\SharedContext\Domain\Service\Helper\BreadcrumsServiceInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\SharedContext\Presenter\Service\SafeRedirectUrlResolver;

#[Route('/email-request', name: 'password.request', methods: ['GET', 'POST'])]
class EmailRequestController extends AbstractController
{
    public function __construct(private readonly BreadcrumsServiceInterface $breadcrumsService, private readonly ResetPasswordThrottle $throttle)
    {
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(Request $request): Response
    {
        $title = "Vérification d'email";
        $this->breadcrumsService->addBreadcrumb($title, $this->generateUrl('app_login'));
        $command = new EmailRequestCommand();
        $form = $this->createForm(EmailRequestType::class, $command);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $email = strtolower(trim((string) $command->email));
                if ($this->throttle->consume($email, $request->getClientIp())) {
                    $this->handleCommand(command: $command);
                }
                return $this->redirectToRoute('app_check_email');
            } catch (UserFacingError $e) {
                $this->flash()->errorFromException($e);
                return $this->redirect(
                    SafeRedirectUrlResolver::resolve($request, $this->generateUrl('app_login'))
                );
            }
        }
        return $this->render('auths/reset_password/request.html.twig', [
            'title' => $title,
            'user' => $command,
            'requestForm' => $form,
            'breadcrumbs' => $this->breadcrumsService->getBreadcrumbs(),
        ]);
    }
}

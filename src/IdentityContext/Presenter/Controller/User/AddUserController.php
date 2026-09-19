<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Controller\User;

use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\AddUserCommand;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\IdentityContext\Presenter\Form\User\AddUserFormType;
use Websymphonie\SharedContext\Domain\Service\Helper\BreadcrumsServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\SharedContext\Presenter\Service\SafeRedirectUrlResolver;

#[Route('/users/add', name: 'app_user_add', methods: ['GET', 'POST'])]
#[HasGroupAccess(RoleGroupEnum::USER_ACCOUNT)]
final class AddUserController extends AbstractController
{
    /**
     * @throws Throwable
     */
    #[IsGranted(new Expression('is_granted("ROLE_CREATE")'))]
    public function __invoke(
        Request                    $request,
        LoggerInterface            $logger,
        BreadcrumsServiceInterface $breadcrumsService
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $title = "Ajouter un utilisateur";
        $breadcrumsService->addBreadcrumb("Utilisateurs", $this->generateUrl('app_user_index'));
        $breadcrumsService->addBreadcrumb($title, $this->generateUrl('app_user_add'));
        $command = new AddUserCommand();
        $form = $this->createForm(AddUserFormType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handleCommand($command);
                $this->flash()->success('Utilisateur créé avec succès.');
            } catch (UserFacingError $e) {
                $this->flash()->errorFromException($e);
                $logger->error('ERROR USER ADD', ['exception' => $e]);
            }
            return new RedirectResponse(SafeRedirectUrlResolver::resolve(
                $request,
                $this->generateUrl('app_user_index')
            ));
        }

        return $this->render('identity/user/add.html.twig', [
            'title' => $title,
            'command' => $command,
            'form' => $form->createView(),
            'breadcrumbs' => $breadcrumsService->getBreadcrumbs(),
        ]);
    }
}

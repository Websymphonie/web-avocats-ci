<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Controller\User;

use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Presenter\Form\User\UpdateUserFormType;
use Websymphonie\SharedContext\Domain\Service\Helper\BreadcrumsServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\SharedContext\Presenter\Service\SafeRedirectUrlResolver;

#[Route('/users/{id}/update', name: 'app_user_update', requirements: ['id' => Requirement::DIGITS], methods: ['GET', 'POST'])]
#[HasGroupAccess(RoleGroupEnum::USER_ACCOUNT)]
final class UpdateUserController extends AbstractController
{
    /**
     * @throws Throwable
     */
    #[IsGranted(new Expression('is_granted("ROLE_EDIT")'))]
    public function __invoke(
        Request                      $request,
        int                          $id,
        LoggerInterface              $logger,
        UserModelRepositoryInterface $rep,
        BreadcrumsServiceInterface   $breadcrumsService
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $command = $rep->createCommandFromUser($id);

        $title = sprintf("%s", $command->email);
        $breadcrumsService->addBreadcrumb("Utilisateurs", $this->generateUrl('app_user_index'));
        $breadcrumsService->addBreadcrumb($title, $this->generateUrl('app_user_update', ['id' => $id]));
        $form = $this->createForm(UpdateUserFormType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handleCommand($command);
                $this->flash()->success('Utilisateur modifié avec succès.');
            } catch (UserFacingError $e) {
                $this->flash()->errorFromException($e);
                $logger->error('ERROR USER UPDATE', ['exception' => $e]);
            }
            return new RedirectResponse(SafeRedirectUrlResolver::resolve(
                $request,
                $this->generateUrl('app_user_index')
            ));
        }
        return $this->render('identity/user/update.html.twig', [
            'title' => $title,
            'command' => $command,
            'form' => $form->createView(),
            'breadcrumbs' => $breadcrumsService->getBreadcrumbs(),
        ]);
    }
}

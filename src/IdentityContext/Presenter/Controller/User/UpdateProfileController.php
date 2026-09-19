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
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Presenter\Form\User\ProfileFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Domain\Service\Helper\BreadcrumsServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\SharedContext\Presenter\Service\SafeRedirectUrlResolver;

#[Route('/users/{id}/profile', name: 'app_user_profile_update', requirements: ['id' => Requirement::DIGITS], methods: ['GET', 'POST'])]
#[HasGroupAccess(RoleGroupEnum::ALL)]
final class UpdateProfileController extends AbstractController
{
    public function __construct(private readonly UserModelRepositoryInterface $repository)
    {
    }

    /**
     * @throws Throwable
     */
    #[IsGranted(new Expression('is_granted("ROLE_EDIT")'))]
    public function __invoke(
        Request                      $request,
        int                          $id,
        UserModelRepositoryInterface $repository,
        LoggerInterface              $logger,
        BreadcrumsServiceInterface   $breadcrumsService
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $command = $this->repository->createCommandProfileFromUser($id);

        /** @var User $user */
        $user = $this->getUser();
        if ($user->getId() !== $id) {
            return $this->redirectToRoute('app_user_profile_update', ['id' => $user->getId()]);
        }

        $title = 'Mon profil';
        $breadcrumsService->addBreadcrumb($title, $this->generateUrl('app_user_profile_update', ['id' => $id]));
        $form = $this->createForm(ProfileFormType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handleCommand($command);
                $this->flash()->success('Informations modifiées avec succès.');
            } catch (UserFacingError $e) {
                $this->flash()->errorFromException($e);
                $logger->error('ERROR PROFILE UPDATE', ['exception' => $e]);
            }
            return new RedirectResponse(SafeRedirectUrlResolver::resolve(
                $request,
                $this->generateUrl('app_user_profile_update', ['id' => $id])
            ));
        }
        return $this->render('identity/user/update_profile.html.twig', [
            'title' => $title,
            'command' => $command,
            'form' => $form->createView(),
            'breadcrumbs' => $breadcrumsService->getBreadcrumbs(),
        ]);
    }
}

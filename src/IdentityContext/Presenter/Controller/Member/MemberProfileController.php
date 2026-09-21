<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Controller\Member;

use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Presenter\Form\User\ProfileFormType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\SharedContext\Presenter\Service\SafeRedirectUrlResolver;

#[Route('/espace/profil', name: 'member_profile', methods: ['GET', 'POST'])]
#[HasGroupAccess(RoleGroupEnum::ALL)]
final class MemberProfileController extends AbstractController
{
    public function __construct(private readonly UserModelRepositoryInterface $repository)
    {
    }

    /**
     * @throws Throwable
     */
    #[IsGranted(new Expression('is_granted("ROLE_EDIT")'))]
    public function __invoke(Request $request, LoggerInterface $logger): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        if (!$user instanceof User || $user->getId() === null) {
            throw $this->createAccessDeniedException();
        }

        $command = $this->repository->createCommandProfileFromUser($user->getId());
        $form = $this->createForm(ProfileFormType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handleCommand($command);
                $this->flash()->success('Informations modifiées avec succès.');
            } catch (UserFacingError $exception) {
                $this->flash()->errorFromException($exception);
                $logger->error('ERROR MEMBER PROFILE UPDATE', ['exception' => $exception]);
            }

            return new RedirectResponse(SafeRedirectUrlResolver::resolve(
                $request,
                $this->generateUrl('member_profile'),
            ));
        }

        return $this->render('member/profile/index.html.twig', [
            'title' => 'Mon profil',
            'command' => $command,
            'form' => $form->createView(),
        ]);
    }
}

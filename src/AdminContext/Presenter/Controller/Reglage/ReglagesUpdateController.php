<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Presenter\Controller\Reglage;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\AdminContext\Domain\Repository\Reglage\ReglageModelRepository;
use Websymphonie\AdminContext\Presenter\Form\Reglage\UpdateReglageFormType;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\SharedContext\Presenter\Service\SafeRedirectUrlResolver;

#[Route(path: '/reglages/{id}/update', name: 'admin_reglages_update', requirements: ['id' => Requirement::DIGITS], methods: ['POST'])]
#[HasGroupAccess(RoleGroupEnum::REGLAGES)]
class ReglagesUpdateController extends AbstractController
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[IsGranted(new Expression('is_granted("ROLE_EDIT")'))]
    public function __invoke(
        Request                $request,
        int                    $id,
        ReglageModelRepository $repository,
        LoggerInterface        $logger
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $command = $repository->createCommandFromReglage($id);

        $form = $this->createForm(UpdateReglageFormType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handleCommand($command);
                $this->flash()->success('Réglage modifié avec succès.');
            } catch (UserFacingError $e) {
                $this->flash()->errorFromException($e);
                $logger->error('ERROR REGLAGE UPDATE', ['exception' => $e]);
            }

        }
        return new RedirectResponse(SafeRedirectUrlResolver::resolve(
            $request,
            $this->generateUrl('admin_reglages_index')
        ));
    }
}

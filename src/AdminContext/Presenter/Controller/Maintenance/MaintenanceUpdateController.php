<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Presenter\Controller\Maintenance;

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
use Websymphonie\AdminContext\Application\Usecase\Command\Maintenance\UpdateMaintenanceCommand;
use Websymphonie\AdminContext\Domain\Repository\Maintenance\MaintenanceModelRepository;
use Websymphonie\AdminContext\Presenter\Form\Maintenance\MaintenancesType;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\SharedContext\Presenter\Service\SafeRedirectUrlResolver;

#[Route(path: '/maintenance/{id}/update', name: 'admin_maintenance_edit', requirements: ['id' => Requirement::DIGITS], methods: ['POST'])]
#[HasGroupAccess(RoleGroupEnum::MAINTENANCE)]
class MaintenanceUpdateController extends AbstractController
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[IsGranted(new Expression('is_granted("ROLE_EDIT")'))]
    public function __invoke(
        Request                    $request,
        int                        $id,
        MaintenanceModelRepository $repository,
        LoggerInterface            $logger
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $command = new UpdateMaintenanceCommand(id: $id);
        $form = $this->createForm(MaintenancesType::class, $command, ['data' => $command]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handleCommand($command);
                $this->flash()->success('Mode maintenance mis à jour avec succès.');
            } catch (UserFacingError $e) {
                $this->flash()->errorFromException($e);
                $logger->error('ERROR PARAMETRE UPDATE', ['exception' => $e]);
            }
        }
        return new RedirectResponse(SafeRedirectUrlResolver::resolve(
            $request,
            $this->generateUrl('app_admin')
        ));
    }
}

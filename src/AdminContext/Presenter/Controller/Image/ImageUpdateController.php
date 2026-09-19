<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Presenter\Controller\Image;

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
use Websymphonie\AdminContext\Domain\Repository\Image\ImageModelRepositoryInterface;
use Websymphonie\AdminContext\Presenter\Form\Parametre\UpdateImageFormType;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\SharedContext\Presenter\Service\SafeRedirectUrlResolver;

#[Route(path: '/images/{id}/update', name: 'app_images_edit', requirements: ['id' => Requirement::DIGITS], methods: ['POST'])]
#[HasGroupAccess(RoleGroupEnum::IMAGES)]
class ImageUpdateController extends AbstractController
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[IsGranted(new Expression('is_granted("ROLE_EDIT")'))]
    public function __invoke(
        Request                       $request,
        int                           $id,
        ImageModelRepositoryInterface $repository,
        LoggerInterface               $logger
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $command = $repository->createCommandFromImage($id);
        $form = $this->createForm(UpdateImageFormType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handleCommand($command);
                $this->flash()->success('Image modifiée avec succès.');
            } catch (UserFacingError $e) {
                $this->flash()->errorFromException($e);
                $logger->error('ERROR IMAGE UPDATE', ['exception' => $e]);
            }
        }
        return new RedirectResponse(SafeRedirectUrlResolver::resolve(
            $request,
            $this->generateUrl('app_images_index')
        ));
    }
}

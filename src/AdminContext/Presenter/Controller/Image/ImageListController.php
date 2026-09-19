<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Presenter\Controller\Image;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\AdminContext\Application\Usecase\Query\Image\ImageListQuery;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Domain\Service\Helper\BreadcrumsServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\SharedContext\Presenter\ViewModel\ListViewModel;

#[Route(path: '/images/list', name: 'app_images_index', methods: ['GET'])]
#[HasGroupAccess(RoleGroupEnum::IMAGES)]
class ImageListController extends AbstractController
{

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[IsGranted(new Expression('is_granted("ROLE_LIST")'))]
    public function __invoke(
        Request                    $request,
        BreadcrumsServiceInterface $breadcrumsService,
        ContextServiceInterface    $contextService,
        #[MapQueryString]
        ImageListQuery             $query
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $title = "Gestion des images";
        $breadcrumsService->addBreadcrumb($title, $this->generateUrl('app_images_index'));

        /** @var ListViewModel $images */
        $images = $this->handleQuery($query);

        return $this->render('admin/image/index.html.twig', [
            'title' => $title,
            'query' => $query,
            'images' => $images->items,
            'breadcrumbs' => $breadcrumsService->getBreadcrumbs(),
        ]);
    }
}

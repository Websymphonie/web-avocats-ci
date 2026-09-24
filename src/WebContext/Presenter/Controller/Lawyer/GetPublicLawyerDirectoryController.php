<?php

declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Controller\Lawyer;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Websymphonie\LawyerContext\Application\Usecase\Query\GetPublicCabinetDirectoryQuery;
use Websymphonie\LawyerContext\Application\Usecase\Query\GetPublicLawyerDirectoryQuery;
use Websymphonie\LawyerContext\Domain\Model\LawyerDirectoryResult;
use Websymphonie\LawyerContext\Domain\Model\PublicCabinetDirectoryEntry;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/avocats', name: 'web_lawyer_directory_')]
final class GetPublicLawyerDirectoryController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $mediaUrls)
    {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $name = $this->queryText($request, 'name');
        $cabinet = $this->queryText($request, 'cabinet');
        if (Uuid::isValid($cabinet)) {
            $cabinet = Uuid::fromString($cabinet)->toRfc4122();
        }
        $location = $this->queryText($request, 'location');

        /** @var LawyerDirectoryResult $directory */
        $directory = $this->handleQuery(new GetPublicLawyerDirectoryQuery(
            name: $name,
            cabinet: $cabinet,
            location: $location,
            page: max(1, $request->query->getInt('page', 1)),
            limit: 12,
        ));

        $portraitIds = array_values(array_unique(array_filter(array_map(
            static fn ($lawyer): ?int => $lawyer->portraitMediaId,
            $directory->items,
        ))));

        /** @var list<PublicCabinetDirectoryEntry> $cabinetOptions */
        $cabinetOptions = $this->handleQuery(new GetPublicCabinetDirectoryQuery());
        $selectedCabinet = null;
        foreach ($cabinetOptions as $cabinetOption) {
            if ($cabinetOption->publicUuid === $cabinet) {
                $selectedCabinet = $cabinetOption;
                break;
            }
        }

        return $this->render('web/lawyers/index.html.twig', [
            'directory' => $directory,
            'cabinetOptions' => $cabinetOptions,
            'selectedCabinet' => $selectedCabinet,
            'mediaUrls' => $this->mediaUrls->resolveMany($portraitIds),
            'filters' => ['name' => $name, 'cabinet' => $cabinet, 'location' => $location],
            'hasFilters' => $name !== '' || $cabinet !== '' || $location !== '',
        ]);
    }

    private function queryText(Request $request, string $parameter): string
    {
        return mb_substr(trim($request->query->getString($parameter)), 0, 120);
    }
}

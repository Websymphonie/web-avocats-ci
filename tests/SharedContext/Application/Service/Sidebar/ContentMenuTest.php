<?php

declare(strict_types=1);

namespace Websymphonie\Tests\SharedContext\Application\Service\Sidebar;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Websymphonie\SharedContext\Application\Service\Sidebar\Modules\ContentMenu;
use Websymphonie\SharedContext\Application\Service\Sidebar\Service\SidebarMenuService;

final class ContentMenuTest extends TestCase
{
    public function testContentEntriesAreGroupedByEditorialType(): void
    {
        $items = ContentMenu::items();

        self::assertSame(
            ['Actualités', 'Événements', 'Médias', 'Documents', 'Pages statiques', 'Tags'],
            array_map(static fn ($item): string => $item->label, $items),
        );
        self::assertSame(
            ['Liste des actualités', 'Catégories'],
            array_map(static fn ($item): string => $item->label, $items[0]->children),
        );
        self::assertSame(
            ['Liste des événements', 'Catégories'],
            array_map(static fn ($item): string => $item->label, $items[1]->children),
        );
        self::assertSame(
            ['Vidéos', 'Galeries photos'],
            array_map(static fn ($item): string => $item->label, $items[2]->children),
        );
    }

    public function testParentRemainsVisibleWhenOnlyOneChildPermissionIsGranted(): void
    {
        $authorizationChecker = $this->createStub(AuthorizationCheckerInterface::class);
        $authorizationChecker
            ->method('isGranted')
            ->willReturnCallback(static fn (string $permission): bool => $permission === 'CONTENT_NEWS_VIEW');

        $service = new SidebarMenuService([new ContentMenu()], $authorizationChecker);
        $groups = $service->getMenuEntries([]);
        $contentItems = $groups['Contenu'];

        self::assertCount(1, $contentItems);
        self::assertSame('Actualités', $contentItems[0]->label);
        self::assertSame(['Liste des actualités'], array_map(static fn ($item): string => $item->label, $contentItems[0]->children));
    }
}

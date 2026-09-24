<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Factory;

use Websymphonie\ContentContext\Domain\Model\PagePersonEntry;
use Websymphonie\ContentContext\Domain\Model\PagePersonGroup;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\PagePerson\PagePersonEntryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\PagePerson\PagePersonGroupEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Page\PageEntity;

final class PagePersonGroupFactory
{
    public function fromEntity(PagePersonGroupEntity $entity): PagePersonGroup
    {
        $entries = [];
        foreach ($entity->getEntries() as $entry) {
            $entries[] = new PagePersonEntry(
                $entry->getId() ?? 0,
                $entry->getUuidAsString() ?? '',
                $entry->getKey(),
                $entry->getDisplayName(),
                $entry->getRoleLabel(),
                $entry->getPeriodLabel(),
                $entry->getPortraitMediaId(),
                $entry->getLinkUrl(),
                $entry->getSortOrder(),
            );
        }

        usort($entries, static fn (PagePersonEntry $a, PagePersonEntry $b): int => [$a->sortOrder, $a->displayName] <=> [$b->sortOrder, $b->displayName]);

        return new PagePersonGroup(
            $entity->getId() ?? 0,
            $entity->getUuidAsString() ?? '',
            $entity->getTitle(),
            $entity->getKey(),
            $entity->getSortOrder(),
            $entries,
        );
    }

    public function toEntity(PagePersonGroup $group, PageEntity $page, ?PagePersonGroupEntity $entity = null): PagePersonGroupEntity
    {
        $entity ??= (new PagePersonGroupEntity())->setPage($page);
        $entity->setTitle($group->title)->setKey($group->key)->setSortOrder($group->sortOrder);
        $existingEntries = [];
        foreach ($entity->getEntries() as $entry) {
            $existingEntries[$entry->getKey()] = $entry;
        }
        $requestedKeys = array_map(static fn (PagePersonEntry $entry): string => $entry->key, $group->entries);
        foreach ($existingEntries as $key => $entry) {
            if (!in_array($key, $requestedKeys, true)) {
                $entity->removeEntry($entry);
            }
        }
        foreach ($group->entries as $entry) {
            $entryEntity = $existingEntries[$entry->key] ?? new PagePersonEntryEntity();
            $entryEntity->setKey($entry->key)
                ->setDisplayName($entry->displayName)
                ->setRoleLabel($entry->roleLabel)
                ->setPeriodLabel($entry->periodLabel)
                ->setPortraitMediaId($entry->portraitMediaId)
                ->setLinkUrl($entry->linkUrl)
                ->setSortOrder($entry->sortOrder);
            $entity->addEntry($entryEntity);
        }

        return $entity;
    }
}

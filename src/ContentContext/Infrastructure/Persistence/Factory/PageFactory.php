<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Factory;

use Websymphonie\ContentContext\Domain\Model\Page;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Page\PageEntity;

final class PageFactory
{
    public function fromEntity(PageEntity $entity): Page
    {
        return new Page(
            $entity->getId() ?? 0,
            $entity->getUuidAsString() ?? '',
            $entity->getTitle(),
            $entity->getSlug(),
            $entity->getContent(),
            $entity->getStatus(),
            $entity->getPublishedAt(),
            $entity->getCreatedAt(),
            $entity->getUpdatedAt(),
            $entity->getCoverMediaId(),
            $entity->getGroup(),
            $entity->getSortOrder(),
        );
    }

    public function toEntity(Page $page, ?PageEntity $entity = null): PageEntity
    {
        $entity ??= new PageEntity();
        return $entity
            ->setTitle($page->title)
            ->setSlug($page->slug)
            ->setContent($page->content)
            ->setStatus($page->status)
            ->setPublishedAt($page->publishedAt)
            ->setCoverMediaId($page->coverMediaId)
            ->setGroup($page->group)
            ->setSortOrder($page->sortOrder);
    }
}

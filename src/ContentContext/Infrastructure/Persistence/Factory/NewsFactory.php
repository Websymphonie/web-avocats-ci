<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Factory;

use Websymphonie\ContentContext\Domain\Model\News;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\News\NewsEntity;

final class NewsFactory
{
    public function fromEntity(NewsEntity $entity): News
    {
        return new News(
            id: $entity->getId(),
            uuid: $entity->getUuidAsString() ?? '',
            title: $entity->getTitle(),
            slug: $entity->getSlug(),
            excerpt: $entity->getExcerpt(),
            body: $entity->getBody(),
            status: $entity->getStatus(),
            publishedAt: $entity->getPublishedAt(),
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt(),
        );
    }

    public function toEntity(News $model, ?NewsEntity $entity = null): NewsEntity
    {
        $entity ??= new NewsEntity();
        $entity->setTitle($model->title)
            ->setSlug($model->slug)
            ->setExcerpt($model->excerpt)
            ->setBody($model->body)
            ->setStatus($model->status)
            ->setPublishedAt($model->publishedAt);

        return $entity;
    }
}

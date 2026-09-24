<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Infrastructure\Persistence\Factory;

use PHPUnit\Framework\TestCase;
use Websymphonie\LearningContext\Domain\Enum\VideoProvider;
use Websymphonie\LearningContext\Domain\Model\ExternalVideoSource;
use Websymphonie\LearningContext\Domain\Model\Lesson;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Lesson\LessonEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Factory\LessonFactory;

final class LessonFactoryTest extends TestCase
{
    public function testMapsProviderNeutralSourceAndPreservesLegacyVideoUrl(): void
    {
        $factory = new LessonFactory();
        $entity = (new LessonEntity())
            ->setModuleId(7)
            ->setTitle('Leçon')
            ->setVideoUrl('https://www.youtube.com/watch?v=legacy-id')
            ->setVideoProvider(VideoProvider::YOUTUBE->value)
            ->setExternalVideoId('M7lc1UVf-VE');

        $model = $factory->fromEntity($entity);

        self::assertSame(VideoProvider::YOUTUBE, $model->videoSource->provider);
        self::assertSame('M7lc1UVf-VE', $model->videoSource->externalId);

        $factory->toEntity($model, $entity);
        self::assertSame('https://www.youtube.com/watch?v=legacy-id', $entity->getVideoUrl());
    }

    public function testClearingVideoSourceDoesNotEraseHistoricalVideoUrlColumn(): void
    {
        $factory = new LessonFactory();
        $entity = (new LessonEntity())
            ->setModuleId(7)
            ->setTitle('Leçon')
            ->setVideoUrl('legacy-reference-kept-for-audit');
        $model = new Lesson(0, '', 7, 'Leçon', videoSource: new ExternalVideoSource(VideoProvider::MUX, 'asset-01'));
        $model->update('Leçon', null, '', null);

        $factory->toEntity($model, $entity);

        self::assertNull($entity->getVideoProvider());
        self::assertNull($entity->getExternalVideoId());
        self::assertSame('legacy-reference-kept-for-audit', $entity->getVideoUrl());
    }
}

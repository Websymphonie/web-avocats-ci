<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Fixtures;

use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Websymphonie\LearningContext\Domain\Enum\LiveDeliveryMode;
use Websymphonie\LearningContext\Domain\Enum\VideoProvider;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\CourseModule\CourseModuleEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Lesson\LessonEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\LiveTrainingDetails\LiveTrainingDetailsEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Training\TrainingEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\TrainingCategory\TrainingCategoryEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\TrainingTag\TrainingTagEntity;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Entity\MediaEntity;
use Websymphonie\PaymentContext\Application\Service\CurrencyCatalogInterface;
use Websymphonie\PaymentContext\Infrastructure\Persistence\Doctrine\Entity\TrainingOffer\TrainingOfferEntity;

final class DemoLearningFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    private const DEMO_YOUTUBE_VIDEO_ID = 'M7lc1UVf-VE';

    public function __construct(
        private readonly CurrencyCatalogInterface $currencies,
    ) {
    }

    public static function getGroups(): array
    {
        return ['demo'];
    }

    public function getDependencies(): array
    {
        return [DemoLearningTaxonomyFixtures::class, \Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Fixtures\DemoMediaFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        DbLogListener::withoutLogging(function () use ($manager): void {
            $trainings = $this->loadTrainings($manager);
            $manager->flush();
            $this->loadLiveDetails($manager, $trainings);
            $this->loadCourseStructure($manager, $trainings);
            $this->loadOffers($manager, $trainings);
            $manager->flush();
        });
    }

    /** @return list<TrainingEntity> */
    private function loadTrainings(ObjectManager $manager): array
    {
        $trainings = [];
        $categoryRefs = ['formation_continue', 'deontologie', 'procedure', 'pratique_professionnelle', 'droit_des_affaires', 'droit_penal', 'numerique_et_droit'];
        $tagRefs = ['deontologie', 'procedure_civile', 'procedure_penale', 'droit_des_societes', 'droit_du_travail', 'pratique_du_cabinet', 'numerique', 'plaidoirie'];
        $now = new DateTimeImmutable();

        for ($index = 1; $index <= 14; ++$index) {
            $isCourse = $index <= 8;
            $ordinal = $isCourse ? $index : $index - 8;
            $type = $isCourse ? TrainingType::COURSE : TrainingType::LIVE;
            $status = ($isCourse && $ordinal <= 5) || (!$isCourse && $ordinal <= 4) ? TrainingStatus::PUBLISHED : TrainingStatus::DRAFT;
            $access = [TrainingAccessType::FREE, TrainingAccessType::PAID, TrainingAccessType::RESTRICTED][$index % 3];
            $visibility = $index % 4 === 0 ? TrainingVisibility::MEMBER : TrainingVisibility::PUBLIC;
            $slug = ($isCourse ? 'cours-' : 'live-') . str_pad((string) $ordinal, 2, '0', STR_PAD_LEFT) . '-' . ($isCourse ? 'pratique-professionnelle' : 'rendez-vous-du-barreau');
            $title = $isCourse ? sprintf('Parcours %02d — pratique professionnelle de l’avocat', $ordinal) : sprintf('Live %02d — rendez-vous du Barreau', $ordinal);
            $training = $manager->getRepository(TrainingEntity::class)->findOneBy(['slug' => $slug]);
            if (!$training instanceof TrainingEntity) {
                $training = new TrainingEntity($type);
            }
            $training->setTitle($title)
                ->setSlug($slug)
                ->setSummary('Formation de démonstration pour tester le catalogue, les filtres et les parcours Learning.')
                ->setDescription(sprintf('<h2>%s</h2><p>Contenu pédagogique fictif destiné aux environnements de démonstration.</p><p>Les informations doivent être adaptées avant toute utilisation éditoriale réelle.</p>', $title))
                ->setVisibility($visibility)
                ->setAccessType($access)
                ->setStatus($status)
                ->setPublishedAt($status === TrainingStatus::PUBLISHED ? $now->modify(sprintf('-%d days', 5 + $index)) : null)
                ->setCoverMediaId($index % 3 === 0 ? null : $this->mediaId($index % 2 === 0 ? 'demo_media_learning_alt' : 'demo_media_learning'));
            $training->replaceCategories([
                $this->trainingCategory('training_category_' . $categoryRefs[$index % count($categoryRefs)]),
                $this->trainingCategory('training_category_' . $categoryRefs[($index + 2) % count($categoryRefs)]),
            ]);
            $training->replaceTags([
                $this->trainingTag('training_tag_' . $tagRefs[$index % count($tagRefs)]),
                $this->trainingTag('training_tag_' . $tagRefs[($index + 3) % count($tagRefs)]),
            ]);
            $manager->persist($training);
            $reference = $isCourse ? 'demo_training_course_' : 'demo_training_live_';
            $this->addReference($reference . str_pad((string) $ordinal, 2, '0', STR_PAD_LEFT), $training);
            $trainings[] = $training;
        }

        return $trainings;
    }

    /** @param list<TrainingEntity> $trainings */
    private function loadLiveDetails(ObjectManager $manager, array $trainings): void
    {
        $now = new DateTimeImmutable();
        $modes = LiveDeliveryMode::cases();
        foreach (array_slice($trainings, 8) as $index => $training) {
            $ordinal = $index + 1;
            $details = $manager->getRepository(LiveTrainingDetailsEntity::class)->findOneBy(['trainingId' => $training->getId()]);
            if (!$details instanceof LiveTrainingDetailsEntity) {
                $details = new LiveTrainingDetailsEntity();
            }
            $mode = $modes[$index % count($modes)];
            $startsAt = $now->modify(sprintf('+%d days', [7, 21, 45, 90, 120, 150][$index]));
            $details->setTrainingId($training->getId() ?? 0)
                ->setStartsAt($startsAt)
                ->setEndsAt($startsAt->modify('+2 hours'))
                ->setDeliveryMode($mode)
                ->setLocation($mode === LiveDeliveryMode::ONLINE ? null : 'Lieu fictif de démonstration — Abidjan')
                ->setJoinUrl($mode === LiveDeliveryMode::IN_PERSON ? null : 'https://example.test/learning/live/' . $ordinal)
                ->setStreamProvider($ordinal === 1 ? VideoProvider::YOUTUBE : null)
                ->setExternalStreamId($ordinal === 1 ? self::DEMO_YOUTUBE_VIDEO_ID : null)
                ->setReplayProvider(null)
                ->setReplayExternalId(null);
            $manager->persist($details);
        }
    }

    /** @param list<TrainingEntity> $trainings */
    private function loadCourseStructure(ObjectManager $manager, array $trainings): void
    {
        foreach (array_slice($trainings, 0, 8) as $trainingIndex => $training) {
            $moduleCount = $trainingIndex < 5 ? 3 : 2;
            for ($modulePosition = 1; $modulePosition <= $moduleCount; ++$modulePosition) {
                $module = $manager->getRepository(CourseModuleEntity::class)->findOneBy(['trainingId' => $training->getId(), 'position' => $modulePosition]);
                if (!$module instanceof CourseModuleEntity) {
                    $module = new CourseModuleEntity();
                }
                $module->setTrainingId($training->getId() ?? 0)
                    ->setTitle(sprintf('Module %d — repères essentiels', $modulePosition))
                    ->setDescription('Module de démonstration structuré pour le parcours COURSE.')
                    ->setPosition($modulePosition);
                $manager->persist($module);
            }
        }
        $manager->flush();

        foreach (array_slice($trainings, 0, 8) as $trainingIndex => $training) {
            $moduleCount = $trainingIndex < 5 ? 3 : 2;
            $lessonsPerModule = $trainingIndex < 5 ? 3 : 2;
            for ($modulePosition = 1; $modulePosition <= $moduleCount; ++$modulePosition) {
                $module = $manager->getRepository(CourseModuleEntity::class)->findOneBy(['trainingId' => $training->getId(), 'position' => $modulePosition]);
                if (!$module instanceof CourseModuleEntity) {
                    continue;
                }
                for ($lessonPosition = 1; $lessonPosition <= $lessonsPerModule; ++$lessonPosition) {
                    $lesson = $manager->getRepository(LessonEntity::class)->findOneBy(['moduleId' => $module->getId(), 'position' => $lessonPosition]);
                    if (!$lesson instanceof LessonEntity) {
                        $lesson = new LessonEntity();
                    }
                    $lesson->setModuleId($module->getId() ?? 0)
                        ->setTitle(sprintf('Leçon %d — mise en pratique', $lessonPosition))
                        ->setSummary('Leçon de démonstration avec contenu riche.')
                        ->setContent(sprintf('<h2>Objectif de la leçon</h2><p>Cette leçon fictive accompagne le module %d et sert à vérifier la lecture du contenu pédagogique.</p><ul><li>Notion clé à retenir.</li><li>Exemple d’application en cabinet.</li></ul>', $modulePosition))
                        ->setVideoProvider(null)
                        ->setExternalVideoId(null)
                        ->setPosition($lessonPosition);
                    if ($trainingIndex === 1 && $modulePosition === 1 && $lessonPosition === 1) {
                        $lesson
                            ->setVideoProvider(VideoProvider::YOUTUBE->value)
                            ->setVideoUrl('https://www.youtube.com/watch?v=' . self::DEMO_YOUTUBE_VIDEO_ID)
                            ->setExternalVideoId(self::DEMO_YOUTUBE_VIDEO_ID);
                    }
                    $manager->persist($lesson);
                }
            }
        }
    }

    /** @param list<TrainingEntity> $trainings */
    private function loadOffers(ObjectManager $manager, array $trainings): void
    {
        if ($this->currencies->findActiveByCode('XOF') === null) {
            return;
        }

        foreach ($trainings as $index => $training) {
            if ($training->getAccessType() !== TrainingAccessType::PAID || $training->getId() === null) {
                continue;
            }
            $offer = $manager->getRepository(TrainingOfferEntity::class)->findOneBy(['trainingId' => $training->getId()]);
            if (!$offer instanceof TrainingOfferEntity) {
                $offer = new TrainingOfferEntity();
            }
            $offer->setTrainingId($training->getId())->setAmount(75000 + ($index * 5000))->setCurrency('XOF')->setActive(true);
            $manager->persist($offer);
        }
    }

    private function mediaId(string $reference): int
    {
        return $this->getReference($reference, MediaEntity::class)->getId() ?? throw new \LogicException(sprintf('Média de fixture sans identifiant : %s', $reference));
    }

    private function trainingCategory(string $name): TrainingCategoryEntity
    {
        return $this->getReference($name, TrainingCategoryEntity::class);
    }

    private function trainingTag(string $name): TrainingTagEntity
    {
        return $this->getReference($name, TrainingTagEntity::class);
    }
}

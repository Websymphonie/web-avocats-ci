<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\TrainingCategory\TrainingCategoryEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\TrainingTag\TrainingTagEntity;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;

final class DemoLearningTaxonomyFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['demo'];
    }

    public function load(ObjectManager $manager): void
    {
        DbLogListener::withoutLogging(function () use ($manager): void {
            foreach ([
                'Formation continue' => 'formation-continue',
                'Déontologie' => 'deontologie',
                'Procédure' => 'procedure',
                'Pratique professionnelle' => 'pratique-professionnelle',
                'Droit des affaires' => 'droit-des-affaires',
                'Droit pénal' => 'droit-penal',
                'Numérique et droit' => 'numerique-et-droit',
            ] as $name => $slug) {
                $category = $manager->getRepository(TrainingCategoryEntity::class)->findOneBy(['slug' => $slug]);
                if (!$category instanceof TrainingCategoryEntity) {
                    $category = new TrainingCategoryEntity();
                }
                $category->setName($name)->setSlug($slug);
                $manager->persist($category);
                $this->addReference('training_category_' . str_replace('-', '_', $slug), $category);
            }

            foreach ([
                'Déontologie' => 'deontologie',
                'Procédure civile' => 'procedure-civile',
                'Procédure pénale' => 'procedure-penale',
                'Droit des sociétés' => 'droit-des-societes',
                'Droit du travail' => 'droit-du-travail',
                'Pratique du cabinet' => 'pratique-du-cabinet',
                'Numérique' => 'numerique',
                'Plaidoirie' => 'plaidoirie',
            ] as $name => $slug) {
                $tag = $manager->getRepository(TrainingTagEntity::class)->findOneBy(['slug' => $slug]);
                if (!$tag instanceof TrainingTagEntity) {
                    $tag = new TrainingTagEntity();
                }
                $tag->setName($name)->setSlug($slug);
                $manager->persist($tag);
                $this->addReference('training_tag_' . str_replace('-', '_', $slug), $tag);
            }

            $manager->flush();
        });
    }
}

<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\EventCategory\EventCategoryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\NewsCategory\NewsCategoryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;

final class DemoContentTaxonomyFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['demo'];
    }

    public function load(ObjectManager $manager): void
    {
        DbLogListener::withoutLogging(function () use ($manager): void {
            foreach ([
                'Vie du Barreau' => 'vie-du-barreau',
                'Institution' => 'institution',
                'Profession' => 'profession',
                'Justice' => 'justice',
                'Jurisprudence' => 'jurisprudence',
                'Communication' => 'communication',
            ] as $name => $slug) {
                $category = $manager->getRepository(NewsCategoryEntity::class)->findOneBy(['slug' => $slug]);
                if (!$category instanceof NewsCategoryEntity) {
                    $category = new NewsCategoryEntity();
                }
                $category->setName($name)->setSlug($slug)->setDescription('Catégorie de démonstration pour les actualités du Barreau.');
                $manager->persist($category);
                $this->addReference('news_category_' . str_replace('-', '_', $slug), $category);
            }

            foreach ([
                'Conférences' => 'conferences',
                'Rencontres professionnelles' => 'rencontres-professionnelles',
                'Assemblées' => 'assemblees',
                'Cérémonies' => 'ceremonies',
                'Ateliers' => 'ateliers',
                'Vie institutionnelle' => 'vie-institutionnelle',
            ] as $name => $slug) {
                $category = $manager->getRepository(EventCategoryEntity::class)->findOneBy(['slug' => $slug]);
                if (!$category instanceof EventCategoryEntity) {
                    $category = new EventCategoryEntity();
                }
                $category->setName($name)->setSlug($slug)->setDescription('Catégorie de démonstration pour les événements du Barreau.');
                $manager->persist($category);
                $this->addReference('event_category_' . str_replace('-', '_', $slug), $category);
            }

            foreach ([
                'Déontologie' => 'deontologie',
                'Droit des affaires' => 'droit-des-affaires',
                'Droit pénal' => 'droit-penal',
                'Droit social' => 'droit-social',
                'Procédure' => 'procedure',
                'Numérique' => 'numerique',
                'Vie du Barreau' => 'vie-du-barreau',
                "Profession d'avocat" => 'profession-avocat',
                'Justice' => 'justice',
                'Accès au droit' => 'acces-au-droit',
                'Fonds de Solidarité' => 'fonds-de-solidarite',
            ] as $name => $slug) {
                $tag = $manager->getRepository(TagEntity::class)->findOneBy(['slug' => $slug]);
                if (!$tag instanceof TagEntity) {
                    $tag = new TagEntity();
                }
                $tag->setName($name)->setSlug($slug);
                $manager->persist($tag);
                $this->addReference('content_tag_' . str_replace('-', '_', $slug), $tag);
            }

            $manager->flush();
        });
    }
}

<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\SeedData;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\ContentContext\Domain\Enum\DocumentAccessLevel;

/** Canonical metadata and local source files for the four institutional documents. */
final class InstitutionalDocumentData
{
    public const FUND_TAG_SLUG = 'fonds-de-solidarite';
    public const FUND_TAG_NAME = 'Fonds de Solidarité';

    /**
     * @return list<array{title: string, slug: string, description: string, filename: string, directory: string, accessLevel: DocumentAccessLevel, tagSlug: ?string, tagName: ?string}>
     */
    public static function definitions(): array
    {
        return [
            [
                'title' => 'Règlement intérieur du Barreau de Côte d’Ivoire',
                'slug' => 'reglement-interieur-barreau-cote-ivoire',
                'description' => 'Texte institutionnel général du Barreau de Côte d’Ivoire, comportant une section relative aux règlements pécuniaires.',
                'filename' => 'reglement-interieur-barreau-cote-ivoire.pdf',
                'directory' => 'Carpa',
                'accessLevel' => DocumentAccessLevel::PUBLIC,
                'tagSlug' => null,
                'tagName' => null,
            ],
            [
                'title' => 'Formulaire de demande de prêt',
                'slug' => 'fonds-solidarite-demande-pret',
                'description' => 'Formulaire vierge à remplir pour une demande de prêt au Fonds de Solidarité.',
                'filename' => 'fonds-solidarite-demande-pret.pdf',
                'directory' => 'FundSolidarity',
                'accessLevel' => DocumentAccessLevel::LAWYER,
                'tagSlug' => self::FUND_TAG_SLUG,
                'tagName' => self::FUND_TAG_NAME,
            ],
            [
                'title' => 'Formulaire de demande de don',
                'slug' => 'fonds-solidarite-demande-don',
                'description' => 'Formulaire vierge à remplir pour une demande de don au Fonds de Solidarité.',
                'filename' => 'fonds-solidarite-demande-don.pdf',
                'directory' => 'FundSolidarity',
                'accessLevel' => DocumentAccessLevel::LAWYER,
                'tagSlug' => self::FUND_TAG_SLUG,
                'tagName' => self::FUND_TAG_NAME,
            ],
            [
                'title' => 'Guide du réseau de soins',
                'slug' => 'fonds-solidarite-guide-reseau-soins',
                'description' => 'Guide de l’assuré présentant le réseau de soins de la mutuelle santé du Barreau.',
                'filename' => 'fonds-solidarite-guide-reseau-soins.pdf',
                'directory' => 'FundSolidarity',
                'accessLevel' => DocumentAccessLevel::LAWYER,
                'tagSlug' => self::FUND_TAG_SLUG,
                'tagName' => self::FUND_TAG_NAME,
            ],
        ];
    }

    /** @param array{filename: string, directory: string} $definition */
    public static function sourcePath(array $definition): string
    {
        return dirname(__DIR__) . '/Persistence/Doctrine/Fixtures/Files/' . $definition['directory'] . '/' . $definition['filename'];
    }

    /** @param array{filename: string, directory: string} $definition */
    public static function copyToTemporaryUpload(array $definition, string $prefix): UploadedFile
    {
        $source = self::sourcePath($definition);
        if (!is_file($source) || !is_readable($source)) {
            throw new \RuntimeException(sprintf('Asset documentaire institutionnel introuvable ou illisible : %s', $source));
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), $prefix);
        if ($temporaryPath === false || !copy($source, $temporaryPath)) {
            if ($temporaryPath !== false && is_file($temporaryPath)) {
                unlink($temporaryPath);
            }

            throw new \RuntimeException('Impossible de préparer temporairement un asset documentaire institutionnel.');
        }

        return new UploadedFile($temporaryPath, $definition['filename'], 'application/pdf', null, true);
    }
}

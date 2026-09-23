<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Websymphonie\MediaContext\Application\Service\MediaUploadServiceInterface;
use Websymphonie\MediaContext\Domain\Model\Media;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Entity\MediaEntity;

final class DemoMediaFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(private readonly MediaUploadServiceInterface $mediaUpload)
    {
    }

    public static function getGroups(): array
    {
        return ['demo'];
    }

    public function load(ObjectManager $manager): void
    {
        $sources = [
            'content' => dirname(__DIR__, 6) . '/public/assets/logo.png',
            'content_alt' => dirname(__DIR__, 6) . '/public/assets/avatar.png',
            'content_bar_history' => dirname(__DIR__, 6) . '/public/assets/images/barreau-anciens-batonniers.png',
            'learning' => dirname(__DIR__, 6) . '/public/assets/logo.png',
            'learning_alt' => dirname(__DIR__, 6) . '/public/assets/avatar.png',
            'institution_portrait_florence_loan_messan' => dirname(__DIR__, 6) . '/public/assets/images/institution/portraits/florence-loan-messan.png',
            'institution_portrait_arouna_ouattara' => dirname(__DIR__, 6) . '/public/assets/images/institution/portraits/arouna-ouattara.png',
            'institution_portrait_abbe_yao' => dirname(__DIR__, 6) . '/public/assets/images/institution/portraits/abbe-yao.png',
            'institution_portrait_guizot_bernard_takore' => dirname(__DIR__, 6) . '/public/assets/images/institution/portraits/guizot-bernard-takore.png',
            'institution_portrait_genevieve_sissoko_diallo' => dirname(__DIR__, 6) . '/public/assets/images/institution/portraits/genevieve-sissoko-diallo.png',
            'institution_portrait_marie_francoise_ncho_katchire' => dirname(__DIR__, 6) . '/public/assets/images/institution/portraits/marie-francoise-ncho-katchire.png',
            'institution_portrait_souhalio_lassoman_diomande' => dirname(__DIR__, 6) . '/public/assets/images/institution/portraits/souhalio-lassoman-diomande.png',
            'institution_portrait_allard_marie_ernest_seni' => dirname(__DIR__, 6) . '/public/assets/images/institution/portraits/allard-marie-ernest-seni.png',
            'institution_portrait_bintou_edith_zan_lago' => dirname(__DIR__, 6) . '/public/assets/images/institution/portraits/bintou-edith-zan-lago.png',
            'institution_portrait_josiane_josette_koffi_bredou' => dirname(__DIR__, 6) . '/public/assets/images/institution/portraits/josiane-josette-koffi-bredou.png',
            'institution_portrait_aissata_diabi' => dirname(__DIR__, 6) . '/public/assets/images/institution/portraits/aissata-diabi.png',
            'institution_portrait_nicolas_tompieu_messan' => dirname(__DIR__, 6) . '/public/assets/images/institution/portraits/nicolas-tompieu-messan.png',
            'institution_portrait_ndry_claver_kouadio' => dirname(__DIR__, 6) . '/public/assets/images/institution/portraits/ndry-claver-kouadio.png',
            'institution_portrait_binta_bakayoko_melseaux' => dirname(__DIR__, 6) . '/public/assets/images/institution/portraits/binta-bakayoko-melseaux.png',
            'institution_portrait_yao_philippe_gnimavo' => dirname(__DIR__, 6) . '/public/assets/images/institution/portraits/yao-philippe-gnimavo.png',
            'institution_portrait_roseline_kouame_kodjo_aka' => dirname(__DIR__, 6) . '/public/assets/images/institution/portraits/roseline-kouame-kodjo-aka.png',
            'institution_portrait_maryse_bohoussou' => dirname(__DIR__, 6) . '/public/assets/images/institution/portraits/maryse-bohoussou.png',
            'institution_portrait_brice_tezai_mahan' => dirname(__DIR__, 6) . '/public/assets/images/institution/portraits/brice-tezai-mahan.png',
            'institution_portrait_amadou_camara' => dirname(__DIR__, 6) . '/public/assets/images/institution/portraits/amadou-camara.png',
            'institution_lawyer_directory_demo' => dirname(__DIR__, 6) . '/public/assets/avatar.png',
        ];

        foreach ($sources as $reference => $source) {
            if (!is_file($source)) {
                throw new \RuntimeException(sprintf('Asset de fixture introuvable : %s', $source));
            }

            $storagePrefix = str_starts_with($reference, 'learning')
                ? 'training/covers'
                : (str_starts_with($reference, 'institution_portrait_') ? 'institution/portraits' : (str_starts_with($reference, 'institution_lawyer_') ? 'institution/lawyers' : 'content/covers'));
            $media = $this->uploadCopy($source, $reference, $storagePrefix);
            $entity = $manager->getRepository(MediaEntity::class)->find($media->id);
            if (!$entity instanceof MediaEntity) {
                throw new \RuntimeException(sprintf('Média de fixture introuvable après upload : %s', $reference));
            }
            $this->addReference('demo_media_' . $reference, $entity);
        }
    }

    private function uploadCopy(string $source, string $reference, string $storagePrefix): Media
    {
        $temporary = tempnam(sys_get_temp_dir(), 'avocat-fixture-');
        if ($temporary === false || !copy($source, $temporary)) {
            throw new \RuntimeException(sprintf('Impossible de préparer l’asset de fixture %s.', $reference));
        }

        try {
            return $this->mediaUpload->upload(new UploadedFile($temporary, $reference . '.png', 'image/png', null, true), $storagePrefix);
        } finally {
            if (is_file($temporary)) {
                @unlink($temporary);
            }
        }
    }
}

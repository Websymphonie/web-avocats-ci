<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Service;

use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Model\PagePersonEntryInput;
use Websymphonie\ContentContext\Application\Model\PagePersonGroupInput;
use Websymphonie\ContentContext\Domain\Model\PagePersonEntry;
use Websymphonie\ContentContext\Domain\Model\PagePersonGroup;
use Websymphonie\ContentContext\Domain\Repository\PagePersonGroupRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\MediaUploadServiceInterface;
use Websymphonie\MediaContext\Domain\Model\Media;

final readonly class PagePersonGroupCollectionService
{
    public function __construct(
        private PagePersonGroupRepositoryInterface $repository,
        private MediaUploadServiceInterface $mediaUpload,
        private SluggerInterface $slugger,
    ) {
    }

    /** @param list<PagePersonGroupInput> $inputs */
    public function synchronize(int $pageId, array $inputs): void
    {
        $uploadedMedia = [];
        try {
            $groups = [];
            $groupKeys = [];
            foreach ($inputs as $groupIndex => $input) {
                if (trim($input->title) === '') {
                    continue;
                }
                $groupKey = $this->uniqueKey($input->key !== '' ? $input->key : $input->title, $groupKeys);
                $groupKeys[] = $groupKey;

                $entries = [];
                $entryKeys = [];
                foreach ($input->entries as $entryIndex => $entryInput) {
                    if (trim($entryInput->displayName) === '') {
                        continue;
                    }
                    $entryKey = $this->uniqueKey($entryInput->key !== '' ? $entryInput->key : $entryInput->displayName, $entryKeys);
                    $entryKeys[] = $entryKey;
                    $portraitMediaId = $entryInput->portraitMediaId;
                    if ($entryInput->portrait !== null) {
                        $media = $this->mediaUpload->upload($entryInput->portrait, 'institution/portraits');
                        $uploadedMedia[] = $media;
                        $portraitMediaId = $media->id;
                    } elseif ($entryInput->removePortrait) {
                        $portraitMediaId = null;
                    }

                    $entries[] = new PagePersonEntry(
                        0, '', $entryKey, trim($entryInput->displayName), $entryInput->roleLabel,
                        $entryInput->periodLabel, $portraitMediaId, $entryInput->linkUrl, max(0, $entryInput->sortOrder),
                    );
                }

                $groups[] = new PagePersonGroup(0, '', trim($input->title), $groupKey, max(0, $input->sortOrder), $entries);
            }

            $this->repository->synchronizeForPage($pageId, $groups);
        } catch (\Throwable $exception) {
            foreach ($uploadedMedia as $media) {
                try { $this->mediaUpload->delete($media); } catch (\Throwable) {}
            }
            throw $exception;
        }
    }

    /** @param list<string> $usedKeys */
    private function uniqueKey(string $preferred, array $usedKeys): string
    {
        $base = strtolower($this->slugger->slug(trim($preferred))->toString());
        $base = $base !== '' ? $base : 'personne';
        $key = $base;
        for ($suffix = 2; in_array($key, $usedKeys, true); ++$suffix) {
            $key = $base . '-' . $suffix;
        }

        return $key;
    }
}

<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Presenter\Service;

use Websymphonie\LearningContext\Domain\Enum\VideoProvider;
use Websymphonie\LearningContext\Domain\Model\ExternalVideoSource;

final class YouTubeVideoPresenter
{
    public function embedUrl(?ExternalVideoSource $source): ?string
    {
        if (!$this->isYouTubeSource($source)) {
            return null;
        }

        return 'https://www.youtube-nocookie.com/embed/' . rawurlencode($source->externalId);
    }

    public function referenceUrl(?ExternalVideoSource $source): ?string
    {
        if (!$this->isYouTubeSource($source)) {
            return null;
        }

        return 'https://www.youtube.com/watch?v=' . rawurlencode($source->externalId);
    }

    public function referenceForForm(?ExternalVideoSource $source): ?string
    {
        if ($source?->provider === VideoProvider::MUX && preg_match('/^[A-Za-z0-9_-]{16,64}$/', $source->externalId) === 1) {
            return $source->externalId;
        }

        return $this->referenceUrl($source);
    }

    private function isYouTubeSource(?ExternalVideoSource $source): bool
    {
        return $source !== null
            && $source->provider === VideoProvider::YOUTUBE
            && preg_match('/^[A-Za-z0-9_-]{6,128}$/', $source->externalId) === 1;
    }
}

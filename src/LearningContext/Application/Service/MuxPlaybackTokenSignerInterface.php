<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Service;

use Websymphonie\LearningContext\Application\Exception\VideoPlaybackUnavailableException;

interface MuxPlaybackTokenSignerInterface
{
    /** @throws VideoPlaybackUnavailableException */
    public function signPlayback(string $playbackId): string;
}

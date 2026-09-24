<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

use InvalidArgumentException;
use Websymphonie\LearningContext\Domain\Enum\VideoProvider;

final readonly class ExternalVideoSource
{
    public string $externalId;

    public function __construct(public VideoProvider $provider, string $externalId)
    {
        $externalId = trim($externalId);
        if ($externalId === '' || mb_strlen($externalId) > 128) {
            throw new InvalidArgumentException('La référence vidéo doit contenir entre 1 et 128 caractères.');
        }

        $this->externalId = $externalId;
    }
}

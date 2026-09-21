<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

use DateTimeImmutable;
use Websymphonie\LearningContext\Domain\Enum\LiveStreamProvider;
use Websymphonie\LearningContext\Domain\Enum\LiveDeliveryMode;
use Websymphonie\LearningContext\Domain\Exception\InvalidLiveTrainingDetailsException;

final class LiveTrainingDetails
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public int $trainingId,
        public DateTimeImmutable $startsAt,
        public DateTimeImmutable $endsAt,
        public LiveDeliveryMode $deliveryMode,
        public ?string $location = null,
        public ?string $joinUrl = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
        public ?LiveStreamProvider $streamProvider = null,
        public ?string $externalStreamId = null,
    ) {
        $this->assertValid();
    }

    public function update(
        DateTimeImmutable $startsAt,
        DateTimeImmutable $endsAt,
        LiveDeliveryMode $deliveryMode,
        ?string $location,
        ?string $joinUrl,
        ?LiveStreamProvider $streamProvider = null,
        ?string $externalStreamId = null,
    ): void {
        self::assertValues($startsAt, $endsAt, $deliveryMode, $location, $joinUrl, $streamProvider, $externalStreamId);
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;
        $this->deliveryMode = $deliveryMode;
        $this->location = self::clean($location);
        $this->joinUrl = self::clean($joinUrl);
        $this->streamProvider = $streamProvider;
        $this->externalStreamId = self::clean($externalStreamId);
    }

    public function attachToTraining(int $trainingId): void
    {
        $this->trainingId = $trainingId;
    }

    public function hasJoinUrl(): bool
    {
        return $this->joinUrl !== null;
    }

    public function hasStream(): bool
    {
        return $this->streamProvider !== null && $this->externalStreamId !== null;
    }

    public function streamEmbedUrl(): ?string
    {
        return $this->streamProvider === LiveStreamProvider::YOUTUBE && $this->externalStreamId !== null
            ? 'https://www.youtube-nocookie.com/embed/' . rawurlencode($this->externalStreamId)
            : null;
    }

    public function setYouTubeStream(?string $url): void
    {
        $reference = YouTubeReference::fromHttpsUrl($url);
        if (self::clean($url) !== null && $reference === null) {
            throw new InvalidLiveTrainingDetailsException('Utilisez une URL YouTube HTTPS valide de type watch, youtu.be ou embed.');
        }

        $streamProvider = $reference === null ? null : LiveStreamProvider::YOUTUBE;
        $externalStreamId = $reference?->externalId;
        self::assertValues($this->startsAt, $this->endsAt, $this->deliveryMode, $this->location, $this->joinUrl, $streamProvider, $externalStreamId);
        $this->streamProvider = $streamProvider;
        $this->externalStreamId = $externalStreamId;
    }

    public function assertValid(): void
    {
        self::assertValues($this->startsAt, $this->endsAt, $this->deliveryMode, $this->location, $this->joinUrl, $this->streamProvider, $this->externalStreamId);
    }

    private static function assertValues(
        DateTimeImmutable $startsAt,
        DateTimeImmutable $endsAt,
        LiveDeliveryMode $deliveryMode,
        ?string $location,
        ?string $joinUrl,
        ?LiveStreamProvider $streamProvider,
        ?string $externalStreamId,
    ): void {
        if ($startsAt >= $endsAt) {
            throw new InvalidLiveTrainingDetailsException('La date de fin doit être postérieure à la date de début.');
        }

        if ($deliveryMode->requiresLocation() && self::clean($location) === null) {
            throw new InvalidLiveTrainingDetailsException('Le lieu est requis pour ce mode de diffusion.');
        }

        $externalStreamId = self::clean($externalStreamId);
        if ($streamProvider === null && $externalStreamId !== null) {
            throw new InvalidLiveTrainingDetailsException('Un identifiant de diffusion ne peut pas exister sans fournisseur.');
        }

        if ($streamProvider === LiveStreamProvider::YOUTUBE && ($externalStreamId === null || preg_match('/^[A-Za-z0-9_-]{6,}$/', $externalStreamId) !== 1)) {
            throw new InvalidLiveTrainingDetailsException('L’identifiant de diffusion YouTube est invalide.');
        }

        if ($deliveryMode->requiresJoinUrl() && !self::hasValidStream($streamProvider, $externalStreamId) && !self::isHttpsUrl($joinUrl)) {
            throw new InvalidLiveTrainingDetailsException('Une URL HTTPS valide est requise pour ce mode de diffusion.');
        }

        if ($joinUrl !== null && self::clean($joinUrl) !== null && !self::isHttpsUrl($joinUrl)) {
            throw new InvalidLiveTrainingDetailsException('Le lien de connexion doit être une URL HTTPS valide.');
        }
    }

    private static function hasValidStream(?LiveStreamProvider $streamProvider, ?string $externalStreamId): bool
    {
        return $streamProvider === LiveStreamProvider::YOUTUBE
            && $externalStreamId !== null
            && preg_match('/^[A-Za-z0-9_-]{6,}$/', $externalStreamId) === 1;
    }

    private static function isHttpsUrl(?string $url): bool
    {
        $url = self::clean($url);
        return $url !== null
            && filter_var($url, FILTER_VALIDATE_URL) !== false
            && strtolower((string) parse_url($url, PHP_URL_SCHEME)) === 'https';
    }

    private static function clean(?string $value): ?string
    {
        $value = $value !== null ? trim($value) : null;
        return $value === '' ? null : $value;
    }
}

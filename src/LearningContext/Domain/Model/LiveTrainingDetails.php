<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

use DateTimeImmutable;
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
        public ?ExternalVideoSource $liveSource = null,
        public ?ExternalVideoSource $replaySource = null,
    ) {
        $this->assertValid();
    }

    public function update(
        DateTimeImmutable $startsAt,
        DateTimeImmutable $endsAt,
        LiveDeliveryMode $deliveryMode,
        ?string $location,
        ?string $joinUrl,
        ?ExternalVideoSource $liveSource = null,
        ?ExternalVideoSource $replaySource = null,
    ): void {
        self::assertValues($startsAt, $endsAt, $deliveryMode, $location, $joinUrl, $liveSource);
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;
        $this->deliveryMode = $deliveryMode;
        $this->location = self::clean($location);
        $this->joinUrl = self::clean($joinUrl);
        $this->liveSource = $liveSource;
        $this->replaySource = $replaySource;
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
        return $this->liveSource !== null;
    }

    public function assertValid(): void
    {
        self::assertValues($this->startsAt, $this->endsAt, $this->deliveryMode, $this->location, $this->joinUrl, $this->liveSource);
    }

    private static function assertValues(
        DateTimeImmutable $startsAt,
        DateTimeImmutable $endsAt,
        LiveDeliveryMode $deliveryMode,
        ?string $location,
        ?string $joinUrl,
        ?ExternalVideoSource $liveSource,
    ): void {
        if ($startsAt >= $endsAt) {
            throw new InvalidLiveTrainingDetailsException('La date de fin doit être postérieure à la date de début.');
        }

        if ($deliveryMode->requiresLocation() && self::clean($location) === null) {
            throw new InvalidLiveTrainingDetailsException('Le lieu est requis pour ce mode de diffusion.');
        }

        if ($deliveryMode->requiresJoinUrl() && $liveSource === null && !self::isHttpsUrl($joinUrl)) {
            throw new InvalidLiveTrainingDetailsException('Une URL HTTPS valide est requise pour ce mode de diffusion.');
        }

        if ($joinUrl !== null && self::clean($joinUrl) !== null && !self::isHttpsUrl($joinUrl)) {
            throw new InvalidLiveTrainingDetailsException('Le lien de connexion doit être une URL HTTPS valide.');
        }
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

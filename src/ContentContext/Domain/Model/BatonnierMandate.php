<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Model;

use DateTimeImmutable;
use Websymphonie\ContentContext\Domain\Exception\InvalidBatonnierMandateException;

final class BatonnierMandate
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public string $fullName,
        public ?int $portraitMediaId,
        public DateTimeImmutable $mandateStartedAt,
        public ?DateTimeImmutable $mandateEndedAt,
        public ?string $summary,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->assertValid($fullName, $mandateStartedAt, $mandateEndedAt);
        $this->fullName = trim($fullName);
        $this->summary = self::normalizeSummary($summary);
    }

    public function update(
        string $fullName,
        DateTimeImmutable $mandateStartedAt,
        ?DateTimeImmutable $mandateEndedAt,
        ?string $summary,
    ): void {
        $this->assertValid($fullName, $mandateStartedAt, $mandateEndedAt);
        $this->fullName = trim($fullName);
        $this->mandateStartedAt = $mandateStartedAt;
        $this->mandateEndedAt = $mandateEndedAt;
        $this->summary = self::normalizeSummary($summary);
    }

    public function setPortraitMedia(?int $mediaId): void
    {
        $this->portraitMediaId = $mediaId;
    }

    public function isCurrent(): bool
    {
        return $this->mandateEndedAt === null;
    }

    private function assertValid(string $fullName, DateTimeImmutable $startedAt, ?DateTimeImmutable $endedAt): void
    {
        if (trim($fullName) === '') {
            throw new InvalidBatonnierMandateException('Le nom complet du Bâtonnier est obligatoire.');
        }

        if ($endedAt !== null && $endedAt < $startedAt) {
            throw new InvalidBatonnierMandateException('La fin du mandat doit être postérieure ou égale à son début.');
        }
    }

    private static function normalizeSummary(?string $summary): ?string
    {
        $summary = trim(strip_tags($summary ?? ''));

        return $summary === '' ? null : $summary;
    }
}

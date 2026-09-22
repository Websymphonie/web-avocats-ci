<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Model;

use DateTimeImmutable;
use Websymphonie\ContentContext\Domain\Exception\InvalidCouncilMemberException;

final class CouncilMember
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public string $fullName,
        public string $function,
        public ?int $portraitMediaId,
        public int $sortOrder = 0,
        public ?DateTimeImmutable $mandateStartedAt = null,
        public ?DateTimeImmutable $mandateEndedAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->assertValid($fullName, $function, $sortOrder, $mandateStartedAt, $mandateEndedAt);
        $this->fullName = trim($fullName);
        $this->function = trim($function);
    }

    public function update(
        string $fullName,
        string $function,
        int $sortOrder,
        ?DateTimeImmutable $mandateStartedAt,
        ?DateTimeImmutable $mandateEndedAt,
    ): void {
        $this->assertValid($fullName, $function, $sortOrder, $mandateStartedAt, $mandateEndedAt);
        $this->fullName = trim($fullName);
        $this->function = trim($function);
        $this->sortOrder = $sortOrder;
        $this->mandateStartedAt = $mandateStartedAt;
        $this->mandateEndedAt = $mandateEndedAt;
    }

    public function setPortraitMedia(?int $mediaId): void
    {
        $this->portraitMediaId = $mediaId;
    }

    public function isCurrent(): bool
    {
        return $this->mandateEndedAt === null;
    }

    private function assertValid(string $fullName, string $function, int $sortOrder, ?DateTimeImmutable $startedAt, ?DateTimeImmutable $endedAt): void
    {
        if (trim($fullName) === '') {
            throw new InvalidCouncilMemberException('Le nom complet du membre est obligatoire.');
        }
        if (mb_strlen(trim($fullName)) > 255) {
            throw new InvalidCouncilMemberException('Le nom complet du membre ne peut pas dépasser 255 caractères.');
        }
        if (trim($function) === '') {
            throw new InvalidCouncilMemberException('La fonction du membre est obligatoire.');
        }
        if (mb_strlen(trim($function)) > 255) {
            throw new InvalidCouncilMemberException('La fonction du membre ne peut pas dépasser 255 caractères.');
        }
        if ($sortOrder < 0) {
            throw new InvalidCouncilMemberException('L’ordre d’affichage ne peut pas être négatif.');
        }
        if ($startedAt !== null && $endedAt !== null && $endedAt < $startedAt) {
            throw new InvalidCouncilMemberException('La fin du mandat doit être postérieure ou égale à son début.');
        }
    }
}

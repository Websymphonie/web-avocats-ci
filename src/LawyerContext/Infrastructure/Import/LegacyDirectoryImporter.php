<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Infrastructure\Import;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\Cabinet\CabinetEntity;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\LawyerProfile\LawyerProfileEntity;

/** Local-only import of the extracted public directory. It never performs network requests. */
final class LegacyDirectoryImporter
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /** @return array<string, mixed> */
    public function run(string $lawyersPath, string $cabinetsPath, string $qualityReportPath, bool $write): array
    {
        $lawyersData = $this->readDataset($lawyersPath, 'lawyers');
        $cabinetsData = $this->readDataset($cabinetsPath, 'cabinets');
        $qualityReport = $this->readJson($qualityReportPath);
        $lawyers = $lawyersData['entries'];
        $cabinets = $cabinetsData['entries'];

        $report = [
            'generatedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'mode' => $write ? 'write' : 'dry-run',
            'sources' => [
                'lawyers' => ['path' => $lawyersPath, 'entries' => count($lawyers), 'metadata' => $lawyersData['metadata'] ?? []],
                'cabinets' => ['path' => $cabinetsPath, 'entries' => count($cabinets), 'metadata' => $cabinetsData['metadata'] ?? []],
                'qualityReport' => ['path' => $qualityReportPath, 'extractedAt' => $qualityReport['extractedAt'] ?? null],
            ],
            'strategy' => [
                'cabinetStatus' => 'ACTIVE is required by the current public Cabinet policy; this is a technical publication value, not current institutional verification.',
                'controlledFields' => ['LawyerProfile: displayName, barNumber, professionalPhone, professionalEmail, cabinet, directoryVisible; Cabinet: name, address, phones, email, status, directoryVisible.'],
                'preservedFields' => ['LawyerProfile: public uuid, user link, professionalStatus after creation, portrait, biography and specialization; Cabinet: public uuid, city, country when absent in source, website and description.'],
                'city' => 'Not imported: the source does not provide a reliable structured locality.',
                'portraits' => 'Deferred to DATA-DIR-006; portraitSourceUrl is ignored and no Media is created.',
            ],
            'cabinets' => ['create' => 0, 'update' => 0, 'unchanged' => 0, 'partial' => 0, 'warnings' => 0, 'failures' => 0, 'items' => []],
            'lawyers' => ['create' => 0, 'update' => 0, 'unchanged' => 0, 'potentialMatch' => 0, 'warnings' => 0, 'failures' => 0, 'items' => []],
            'relations' => ['resolved' => 0, 'missing' => 0, 'asymmetric' => 0, 'items' => []],
            'contacts' => ['invalidLawyerEmails' => [], 'invalidCabinetEmails' => [], 'missingEmails' => 0, 'missingPhones' => 0],
            'portraitsDeferred' => 0,
            'databaseWrite' => false,
        ];

        $this->assertUniqueSourceUuids($cabinets, 'Cabinet', $report);
        $this->assertUniqueSourceUuids($lawyers, 'LawyerProfile', $report);
        if ($report['cabinets']['failures'] > 0 || $report['lawyers']['failures'] > 0) {
            return $report;
        }

        $existingCabinets = $this->loadCabinetsWithSourceUuid();
        $cabinetBySourceUuid = $existingCabinets;
        $cabinetChanges = [];
        foreach ($cabinets as $row) {
            $sourceUuid = Uuid::fromString($row['sourceUuid']);
            $key = $sourceUuid->toRfc4122();
            $isNew = !isset($existingCabinets[$key]);
            $entity = $existingCabinets[$key] ?? new CabinetEntity();
            $partial = ($row['detailStatus'] ?? null) !== 'OK';
            if ($partial) {
                $report['cabinets']['partial']++;
                $report['cabinets']['warnings']++;
            }

            $desired = $this->cabinetValues($row, $report);
            $changed = $this->applyCabinetValues($entity, $sourceUuid, $desired);
            $action = $isNew ? 'create' : ($changed ? 'update' : 'unchanged');
            $report['cabinets'][$action]++;
            if ($write && $isNew) {
                $this->entityManager->persist($entity);
            }
            $cabinetBySourceUuid[$key] = $entity;
            $report['cabinets']['items'][] = ['legacySourceUuid' => $key, 'name' => $entity->getName(), 'action' => $action, 'partial' => $partial];
        }
        if ($report['cabinets']['failures'] > 0) {
            $this->entityManager->clear();

            return $report;
        }

        $existingProfiles = $this->loadProfilesWithSourceUuid();
        $unlinkedProfiles = $this->loadProfilesWithoutSourceUuid();
        $profileCandidates = $this->indexPotentialProfiles($unlinkedProfiles);
        $pendingProfiles = [];

        foreach ($lawyers as $row) {
            $sourceUuid = Uuid::fromString($row['sourceUuid']);
            $key = $sourceUuid->toRfc4122();
            $existing = $existingProfiles[$key] ?? null;
            $candidate = $existing === null ? $this->findPotentialMatch($row, $profileCandidates) : null;
            if ($candidate !== null) {
                $report['lawyers']['potentialMatch']++;
                $report['lawyers']['warnings']++;
                $report['lawyers']['items'][] = [
                    'legacySourceUuid' => $key,
                    'displayName' => $row['displayName'],
                    'action' => 'POTENTIAL_MATCH',
                    'candidate' => $candidate,
                ];
                continue;
            }

            $validation = $this->lawyerValues($row, $report);
            if ($validation['failure'] !== null) {
                $report['lawyers']['failures']++;
                $report['lawyers']['items'][] = ['legacySourceUuid' => $key, 'displayName' => $row['displayName'] ?? null, 'action' => 'SKIP', 'failure' => $validation['failure']];
                continue;
            }

            $entity = $existing ?? new LawyerProfileEntity();
            $desired = $validation['values'];
            $cabinetUuid = $this->optionalString($row, 'sourceCabinetUuid');
            $cabinet = $cabinetUuid !== null ? ($cabinetBySourceUuid[strtolower($cabinetUuid)] ?? null) : null;
            $relationMissing = $cabinetUuid !== null && $cabinet === null;
            $asymmetric = $cabinetUuid !== null && isset($cabinetBySourceUuid[strtolower($cabinetUuid)])
                && !in_array($key, $this->stringList($this->findCabinetRow($cabinets, strtolower($cabinetUuid))['memberSourceUuids'] ?? []), true);

            $changed = $this->applyLawyerValues($entity, $sourceUuid, $desired, $cabinet);
            $action = $existing === null ? 'create' : ($changed ? 'update' : 'unchanged');
            $report['lawyers'][$action]++;
            if ($write && $existing === null) {
                $this->entityManager->persist($entity);
            }
            $pendingProfiles[] = $entity;

            if ($cabinetUuid === null || $relationMissing) {
                $report['relations']['missing']++;
                $report['relations']['items'][] = ['lawyerLegacySourceUuid' => $key, 'cabinetLegacySourceUuid' => $cabinetUuid, 'status' => 'MISSING'];
            } else {
                $report['relations']['resolved']++;
            }
            if ($asymmetric) {
                $report['relations']['asymmetric']++;
                $report['relations']['items'][] = ['lawyerLegacySourceUuid' => $key, 'cabinetLegacySourceUuid' => strtolower($cabinetUuid), 'status' => 'WARNING_RELATION_ASYMMETRY'];
                $report['lawyers']['warnings']++;
            }
            if ($validation['emailWarning'] !== null) {
                $report['lawyers']['warnings']++;
                $report['contacts']['invalidLawyerEmails'][] = $validation['emailWarning'];
            }
            if ($this->optionalString($row, 'professionalEmail') === null) {
                $report['contacts']['missingEmails']++;
            }
            if ($this->optionalString($row, 'professionalPhone') === null) {
                $report['contacts']['missingPhones']++;
            }
            if (($row['portraitSourceUrl'] ?? null) !== null) {
                $report['portraitsDeferred']++;
            }
            $report['lawyers']['items'][] = ['legacySourceUuid' => $key, 'displayName' => $entity->getDisplayName(), 'action' => $action];
        }

        if ($write) {
            $connection = $this->entityManager->getConnection();
            $connection->beginTransaction();
            try {
                $this->entityManager->flush();
                $connection->commit();
                $report['databaseWrite'] = true;
            } catch (\Throwable $exception) {
                $connection->rollBack();
                $this->entityManager->clear();
                $report['databaseWrite'] = false;
                $report['lawyers']['failures']++;
                $report['lawyers']['items'][] = ['action' => 'TRANSACTION_FAILED', 'reason' => $exception->getMessage()];
            }
        } else {
            $this->entityManager->clear();
        }

        unset($pendingProfiles);

        return $report;
    }

    /** @return array{metadata?: array<string, mixed>, entries: list<array<string, mixed>>} */
    private function readDataset(string $path, string $type): array
    {
        $data = $this->readJson($path);
        if (!isset($data['entries']) || !is_array($data['entries']) || !array_is_list($data['entries'])) {
            throw new \InvalidArgumentException(sprintf('Le fichier %s doit contenir une liste entries.', $type));
        }

        foreach ($data['entries'] as $index => $entry) {
            if (!is_array($entry)) {
                throw new \InvalidArgumentException(sprintf('Entrée %d invalide dans %s.', $index + 1, $type));
            }
        }

        /** @var list<array<string, mixed>> $entries */
        $entries = $data['entries'];

        return ['metadata' => is_array($data['metadata'] ?? null) ? $data['metadata'] : [], 'entries' => $entries];
    }

    /** @return array<string, mixed> */
    private function readJson(string $path): array
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new \InvalidArgumentException(sprintf('Fichier JSON absent ou illisible : %s', $path));
        }
        $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new \InvalidArgumentException(sprintf('Le fichier JSON doit contenir un objet : %s', $path));
        }

        return $decoded;
    }

    /** @param list<array<string, mixed>> $rows
     *  @param array<string, mixed> $report
     */
    private function assertUniqueSourceUuids(array $rows, string $label, array &$report): void
    {
        $seen = [];
        foreach ($rows as $index => $row) {
            $value = $row['sourceUuid'] ?? null;
            if (!is_string($value) || !Uuid::isValid($value)) {
                $key = $label === 'Cabinet' ? 'cabinets' : 'lawyers';
                $report[$key]['failures']++;
                $report[$key]['items'][] = ['sourceIndex' => $index, 'legacySourceUuid' => $value, 'action' => 'SKIP', 'failure' => 'UUID source absent ou invalide.'];
                continue;
            }
            $normalized = Uuid::fromString($value)->toRfc4122();
            if (isset($seen[$normalized])) {
                $key = $label === 'Cabinet' ? 'cabinets' : 'lawyers';
                $report[$key]['failures']++;
                $report[$key]['items'][] = ['sourceIndex' => $index, 'legacySourceUuid' => $normalized, 'action' => 'CONFLICT', 'failure' => 'UUID source dupliqué dans le dataset.'];
                continue;
            }
            $seen[$normalized] = true;
            $rows[$index]['sourceUuid'] = $normalized;
        }
    }

    /** @return array<string, CabinetEntity> */
    private function loadCabinetsWithSourceUuid(): array
    {
        $entities = $this->entityManager->createQueryBuilder()
            ->select('cabinet')
            ->from(CabinetEntity::class, 'cabinet')
            ->where('cabinet.legacySourceUuid IS NOT NULL')
            ->getQuery()
            ->getResult();
        $indexed = [];
        foreach ($entities as $entity) {
            if ($entity instanceof CabinetEntity && $entity->getLegacySourceUuid() !== null) {
                $indexed[$entity->getLegacySourceUuid()->toRfc4122()] = $entity;
            }
        }

        return $indexed;
    }

    /** @return array<string, LawyerProfileEntity> */
    private function loadProfilesWithSourceUuid(): array
    {
        $entities = $this->entityManager->createQueryBuilder()
            ->select('profile')
            ->from(LawyerProfileEntity::class, 'profile')
            ->where('profile.legacySourceUuid IS NOT NULL')
            ->getQuery()
            ->getResult();
        $indexed = [];
        foreach ($entities as $entity) {
            if ($entity instanceof LawyerProfileEntity && $entity->getLegacySourceUuid() !== null) {
                $indexed[$entity->getLegacySourceUuid()->toRfc4122()] = $entity;
            }
        }

        return $indexed;
    }

    /** @return list<LawyerProfileEntity> */
    private function loadProfilesWithoutSourceUuid(): array
    {
        $entities = $this->entityManager->createQueryBuilder()
            ->select('profile')
            ->from(LawyerProfileEntity::class, 'profile')
            ->where('profile.legacySourceUuid IS NULL')
            ->getQuery()
            ->getResult();

        return array_values(array_filter($entities, static fn (mixed $entity): bool => $entity instanceof LawyerProfileEntity));
    }

    /** @param array<string, mixed> $row
     *  @param array<string, mixed> $report
     *  @return array{name: string, address: ?string, phones: list<string>, email: ?string, country: ?string}
     */
    private function cabinetValues(array $row, array &$report): array
    {
        $name = $this->optionalString($row, 'name') ?? '';
        if ($name === '' || mb_strlen($name) > 255) {
            $report['cabinets']['failures']++;
            $report['cabinets']['items'][] = ['legacySourceUuid' => $row['sourceUuid'], 'action' => 'SKIP', 'failure' => 'Nom Cabinet absent ou supérieur à 255 caractères.'];
        }
        $email = $this->validEmail($this->optionalString($row, 'email'));
        if ($email['invalid'] !== null) {
            $report['cabinets']['warnings']++;
            $report['contacts']['invalidCabinetEmails'][] = ['legacySourceUuid' => $row['sourceUuid'], 'value' => $email['invalid']];
        }
        $country = $this->optionalString($row, 'countryRaw');
        $phones = $this->stringList($row['phones'] ?? []);

        return ['name' => $name, 'address' => $this->optionalString($row, 'addressRaw'), 'phones' => $phones, 'email' => $email['value'], 'country' => $country];
    }

    /** @param array<string, mixed> $row
     *  @param array<string, mixed> $report
     *  @return array{failure: ?string, emailWarning: ?array<string, string>, values: array<string, mixed>}
     */
    private function lawyerValues(array $row, array &$report): array
    {
        $name = $this->optionalString($row, 'displayName') ?? '';
        $barNumber = $this->optionalString($row, 'barNumber');
        $phone = $this->optionalString($row, 'professionalPhone');
        if ($name === '' || mb_strlen($name) > 255) {
            return ['failure' => 'Nom professionnel absent ou supérieur à 255 caractères.', 'emailWarning' => null, 'values' => []];
        }
        if ($barNumber !== null && mb_strlen($barNumber) > 120) {
            return ['failure' => 'Numéro du Barreau supérieur à 120 caractères.', 'emailWarning' => null, 'values' => []];
        }
        if ($phone !== null && mb_strlen($phone) > 80) {
            return ['failure' => 'Téléphone supérieur à 80 caractères.', 'emailWarning' => null, 'values' => []];
        }

        $email = $this->validEmail($this->optionalString($row, 'professionalEmail'));
        $emailWarning = $email['invalid'] !== null
            ? ['type' => 'lawyer', 'legacySourceUuid' => $row['sourceUuid'], 'value' => $email['invalid']]
            : null;

        return [
            'failure' => null,
            'emailWarning' => $emailWarning,
            'values' => ['displayName' => $name, 'barNumber' => $barNumber, 'professionalPhone' => $phone, 'professionalEmail' => $email['value']],
        ];
    }

    /** @param array{name: string, address: ?string, phones: list<string>, email: ?string, country: ?string} $values
     */
    private function applyCabinetValues(CabinetEntity $entity, Uuid $sourceUuid, array $values): bool
    {
        $changed = $entity->getName() !== $values['name']
            || $entity->getAddress() !== $values['address']
            || $entity->getPhones() !== $values['phones']
            || $entity->getEmail() !== $values['email']
            || $entity->getStatus() !== 'ACTIVE'
            || !$entity->isDirectoryVisible()
            || $entity->getLegacySourceUuid()?->toRfc4122() !== $sourceUuid->toRfc4122();
        $entity->setLegacySourceUuid($sourceUuid)
            ->setName($values['name'])
            ->setAddress($values['address'])
            ->setPhones($values['phones'])
            ->setEmail($values['email'])
            ->setStatus('ACTIVE')
            ->setDirectoryVisible(true);
        if ($values['country'] !== null) {
            $changed = $changed || $entity->getCountry() !== $values['country'];
            $entity->setCountry($values['country']);
        }

        return $changed;
    }

    /** @param array<string, mixed> $values */
    private function applyLawyerValues(LawyerProfileEntity $entity, Uuid $sourceUuid, array $values, ?CabinetEntity $cabinet): bool
    {
        $changed = $entity->getDisplayName() !== $values['displayName']
            || $entity->getBarNumber() !== $values['barNumber']
            || $entity->getProfessionalPhone() !== $values['professionalPhone']
            || $entity->getProfessionalEmail() !== $values['professionalEmail']
            || $entity->getCabinet()?->getId() !== $cabinet?->getId()
            || !$entity->isDirectoryVisible()
            || $entity->getLegacySourceUuid()?->toRfc4122() !== $sourceUuid->toRfc4122();
        $entity->setLegacySourceUuid($sourceUuid)
            ->setDisplayName($values['displayName'])
            ->setBarNumber($values['barNumber'])
            ->setProfessionalPhone($values['professionalPhone'])
            ->setProfessionalEmail($values['professionalEmail'])
            ->setCabinet($cabinet)
            ->setDirectoryVisible(true);

        return $changed;
    }

    /** @param list<LawyerProfileEntity> $profiles
     *  @return array<string, array<string, list<array{uuid: string, displayName: string, barNumber: ?string}>>>
     */
    private function indexPotentialProfiles(array $profiles): array
    {
        $index = ['name' => [], 'barNumber' => [], 'email' => []];
        foreach ($profiles as $profile) {
            $publicUuid = $profile->getUuidAsString();
            if ($publicUuid === null) {
                continue;
            }
            $candidate = ['uuid' => $publicUuid, 'displayName' => $profile->getDisplayName(), 'barNumber' => $profile->getBarNumber()];
            $name = mb_strtolower(trim($profile->getDisplayName()));
            if ($name !== '') {
                $index['name'][$name][] = $candidate;
            }
            $barNumber = mb_strtolower(trim($profile->getBarNumber() ?? ''));
            if ($barNumber !== '') {
                $index['barNumber'][$barNumber][] = $candidate;
            }
            $email = mb_strtolower(trim($profile->getProfessionalEmail() ?? ''));
            if ($email !== '') {
                $index['email'][$email][] = $candidate;
            }
        }

        return $index;
    }

    /** @param array<string, mixed> $row
     *  @param array<string, array<string, list<array{uuid: string, displayName: string, barNumber: ?string}>>> $index
     *  @return array{fields: list<string>, profile: array{uuid: string, displayName: string, barNumber: ?string}}|null
     */
    private function findPotentialMatch(array $row, array $index): ?array
    {
        $matchedProfile = null;
        $matchedFields = [];
        $name = mb_strtolower(trim($this->optionalString($row, 'displayName') ?? ''));
        $barNumber = mb_strtolower(trim($this->optionalString($row, 'barNumber') ?? ''));
        $email = mb_strtolower(trim($this->optionalString($row, 'professionalEmail') ?? ''));
        foreach ([
            'name' => $name !== '' ? $name : null,
            'barNumber' => $barNumber !== '' ? $barNumber : null,
            'email' => $email !== '' ? $email : null,
        ] as $field => $value) {
            if ($value === null) {
                continue;
            }
            foreach ($index[$field][$value] ?? [] as $profile) {
                $matchedProfile ??= $profile;
                if ($matchedProfile['uuid'] === $profile['uuid']) {
                    $matchedFields[] = $field;
                }
            }
        }
        if ($matchedProfile !== null) {
            return ['fields' => array_values(array_unique($matchedFields)), 'profile' => $matchedProfile];
        }

        return null;
    }

    /** @param list<array<string, mixed>> $cabinets
     *  @return array<string, mixed>|null
     */
    private function findCabinetRow(array $cabinets, string $sourceUuid): ?array
    {
        foreach ($cabinets as $cabinet) {
            if (strtolower((string) ($cabinet['sourceUuid'] ?? '')) === $sourceUuid) {
                return $cabinet;
            }
        }

        return null;
    }

    /** @param mixed $value
     *  @return list<string>
     */
    private function stringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }
        $values = [];
        foreach ($value as $item) {
            if (is_string($item) && trim($item) !== '') {
                $values[] = trim($item);
            }
        }

        return $values;
    }

    /** @param array<string, mixed> $row */
    private function optionalString(array $row, string $key): ?string
    {
        $value = $row[$key] ?? null;
        if (!is_string($value)) {
            return null;
        }
        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    /** @return array{value: ?string, invalid: ?string} */
    private function validEmail(?string $email): array
    {
        if ($email === null) {
            return ['value' => null, 'invalid' => null];
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return ['value' => null, 'invalid' => $email];
        }

        return ['value' => $email, 'invalid' => null];
    }
}

<?php

declare(strict_types=1);

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Websymphonie\Tools\OldDirectory\FailedCabinetDetailRetry;
use Websymphonie\Tools\OldDirectory\LegacyDirectoryHtmlParser;

require dirname(__DIR__, 3) . '/vendor/autoload.php';
require __DIR__ . '/LegacyDirectoryHtmlParser.php';
require __DIR__ . '/FailedCabinetDetailRetry.php';

const SOURCE_BASE_URL = 'https://app.ordredesavocats-ci.net';
const EXTRACTOR_VERSION = '1.0.0';
const REQUEST_DELAY_MICROSECONDS = 150000;

$arguments = array_slice($argv, 1);
$write = in_array('--write', $arguments, true);
$force = in_array('--force', $arguments, true);
$retryFailedCabinets = in_array('--retry-failed-cabinets', $arguments, true);

if ($retryFailedCabinets && array_diff($arguments, ['--retry-failed-cabinets', '--write']) !== []) {
    fwrite(STDERR, "Le mode ciblé accepte uniquement --retry-failed-cabinets et --write.\n");
    exit(2);
}

if ($force && !$write) {
    fwrite(STDERR, "--force nécessite --write.\n");
    exit(2);
}

$outputDirectory = dirname(__DIR__, 3) . '/src/LawyerContext/Infrastructure/Persistence/Doctrine/Fixtures/Data';
$outputFiles = [
    $outputDirectory . '/lawyers-directory.json',
    $outputDirectory . '/cabinets-directory.json',
    $outputDirectory . '/directory-quality-report.json',
];

if ($write && !$force && !$retryFailedCabinets) {
    $existing = array_values(array_filter($outputFiles, 'is_file'));
    if ($existing !== []) {
        fwrite(STDERR, "Exports déjà présents. Utilisez --force uniquement si leur remplacement est voulu.\n");
        exit(2);
    }
}

$client = HttpClient::create([
    'headers' => ['Accept' => 'text/html'],
    'max_redirects' => 3,
    'timeout' => 20,
]);
$parser = new LegacyDirectoryHtmlParser();

if ($retryFailedCabinets) {
    $cabinetPath = $outputFiles[1];
    $qualityPath = $outputFiles[2];
    foreach ([$cabinetPath, $qualityPath, $outputFiles[0]] as $requiredPath) {
        if (!is_file($requiredPath)) {
            throw new RuntimeException('Les trois exports DATA-DIR-003 doivent exister avant le retry ciblé.');
        }
    }

    $cabinetDocument = json_decode((string) file_get_contents($cabinetPath), true, 512, JSON_THROW_ON_ERROR);
    $lawyerDocument = json_decode((string) file_get_contents($outputFiles[0]), true, 512, JSON_THROW_ON_ERROR);
    $quality = json_decode((string) file_get_contents($qualityPath), true, 512, JSON_THROW_ON_ERROR);
    $failedCabinets = $quality['detailFailures']['cabinets'] ?? [];
    if (!is_array($failedCabinets) || count($failedCabinets) > 7) {
        throw new RuntimeException('Le rapport ne contient pas uniquement les échecs cabinet ciblés attendus.');
    }

    $cabinetIndexes = [];
    foreach ($cabinetDocument['entries'] as $index => $cabinet) {
        $cabinetIndexes[$cabinet['sourceUuid']] = $index;
    }
    $retryService = new FailedCabinetDetailRetry($parser);
    $retryHistory = [];
    $fallbackMemberRelations = [];
    $newFailures = [];
    $retriedCount = 0;

    foreach ($failedCabinets as $failed) {
        $uuid = $failed['sourceUuid'] ?? null;
        if (!is_string($uuid) || !isset($cabinetIndexes[$uuid])) {
            throw new RuntimeException('Un UUID en échec ne correspond à aucune entrée de l’export cabinet.');
        }
        $index = $cabinetIndexes[$uuid];
        $cabinet = $cabinetDocument['entries'][$index];
        if (($cabinet['detailStatus'] ?? null) !== 'FAILED' || ($cabinet['sourceUrl'] ?? null) !== ($failed['url'] ?? null)) {
            throw new RuntimeException(sprintf('URL ou statut inattendu pour le cabinet %s.', $uuid));
        }
        ++$retriedCount;
        fwrite(STDERR, sprintf("Retry ciblé %d/%d : %s\n", $retriedCount, count($failedCabinets), $uuid));

        $result = $retryService->retry($cabinet, static function (string $url) use ($client): array {
            $response = $client->request('GET', $url);
            $status = $response->getStatusCode();

            return ['status' => $status, 'body' => $response->getContent(false)];
        });

        $updatedCabinet = $result['cabinet'];
        $attemptsBeforeTargetedRetry = 4; // Three initial attempts plus the one-request diagnostic probe.
        $updatedCabinet['attemptsBeforeTargetedRetry'] = $attemptsBeforeTargetedRetry;
        $updatedCabinet['detailAttemptsTotal'] = $attemptsBeforeTargetedRetry + $result['attempts'];
        if (!$result['recovered']) {
            $updatedCabinet = $retryService->addLawyerExportMemberReferences($updatedCabinet, $lawyerDocument['entries']);
            $memberFallback = $updatedCabinet['memberSourceUuidsFromLawyerExport'];
            $fallbackMemberRelations[] = [
                'cabinetSourceUuid' => $uuid,
                'source' => 'lawyers-directory.json entries[].sourceCabinetUuid, exact UUID match only',
                'matchedLawyerCount' => count($memberFallback),
            ];
            $newFailures[] = [
                'sourceUuid' => $uuid,
                'url' => $cabinet['sourceUrl'],
                'reason' => $result['failureReason'],
                'attemptsBeforeTargetedRetry' => $attemptsBeforeTargetedRetry,
                'targetedRetryAttempts' => $result['attempts'],
                'totalAttempts' => $attemptsBeforeTargetedRetry + $result['attempts'],
                'observations' => $result['observations'],
            ];
        }
        $retryHistory[] = [
            'sourceUuid' => $uuid,
            'sourceUrl' => $cabinet['sourceUrl'],
            'listingName' => $cabinet['name'] ?? null,
            'previousHttpStatus' => $failed['reason'] ?? null,
            'attemptsBeforeTargetedRetry' => $attemptsBeforeTargetedRetry,
            'targetedRetryAttempts' => $result['attempts'],
            'totalAttempts' => $attemptsBeforeTargetedRetry + $result['attempts'],
            'recovered' => $result['recovered'],
            'observations' => $result['observations'],
        ];
        $cabinetDocument['entries'][$index] = $updatedCabinet;
    }

    $quality['retryCheckedAt'] = (new DateTimeImmutable())->format(DATE_ATOM);
    $quality['sourceLimited'] = $newFailures !== [];
    $quality['retryHistory'] = $retryHistory;
    $quality['fallbackMemberRelations'] = $fallbackMemberRelations;
    $quality['detailFailures']['cabinets'] = $newFailures;
    $cabinetDocument['detailFailures'] = $newFailures;
    $cabinetDocument['metadata']['failedCount'] = count($newFailures);
    $quality['cabinets']['detailsSucceeded'] = count(array_filter($cabinetDocument['entries'], static fn (array $entry): bool => ($entry['detailStatus'] ?? null) === 'OK'));
    $quality['cabinets']['detailsFailed'] = count($newFailures);
    $quality['cabinets']['withoutMembers'] = count(array_filter($cabinetDocument['entries'], static fn (array $entry): bool => ($entry['memberSourceUuids'] ?? []) === []));
    $quality['complete'] = $quality['lawyers']['listingCount'] === $quality['lawyers']['uniqueEntriesExported']
        && $quality['lawyers']['detailsSucceeded'] === $quality['lawyers']['uniqueEntriesExported']
        && $quality['cabinets']['listingCount'] === $quality['cabinets']['uniqueEntriesExported']
        && $quality['cabinets']['detailsSucceeded'] === $quality['cabinets']['uniqueEntriesExported']
        && ($quality['listingFailures']['lawyers'] ?? []) === []
        && ($quality['listingFailures']['cabinets'] ?? []) === []
        && ($quality['lawyers']['duplicateSourceUuids'] ?? []) === []
        && ($quality['cabinets']['duplicateSourceUuids'] ?? []) === [];

    $summary = [
        'mode' => $write ? 'retry-write' : 'retry-dry-run',
        'targeted' => $retriedCount,
        'recovered' => count(array_filter($retryHistory, static fn (array $entry): bool => $entry['recovered'])),
        'stillFailed' => count($newFailures),
        'complete' => $quality['complete'],
        'sourceLimited' => $quality['sourceLimited'],
        'retryHistory' => $retryHistory,
    ];

    if ($write) {
        $encode = static fn (array $data): string => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
        $pendingWrites = [
            $cabinetPath => $encode($cabinetDocument),
            $qualityPath => $encode($quality),
        ];
        $temporaryFiles = [];
        foreach ($pendingWrites as $path => $contents) {
            $temporary = tempnam(dirname($path), '.directory-retry-');
            if ($temporary === false || file_put_contents($temporary, $contents, LOCK_EX) === false) {
                throw new RuntimeException(sprintf('Échec de préparation de %s.', basename($path)));
            }
            $temporaryFiles[$path] = $temporary;
        }
        foreach ($temporaryFiles as $path => $temporary) {
            if (!rename($temporary, $path)) {
                throw new RuntimeException(sprintf('Échec de remplacement de %s.', basename($path)));
            }
        }
    }

    fwrite(STDOUT, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
    exit($quality['complete'] ? 0 : 1);
}

$failures = ['lawyers' => [], 'cabinets' => []];
$listingFailures = ['lawyers' => [], 'cabinets' => []];
$requestCount = 0;

$fetch = static function (string $url) use ($client, &$requestCount): string {
    ++$requestCount;
    if ($requestCount === 1 || $requestCount % 25 === 0) {
        fwrite(STDERR, sprintf("Requêtes HTTP lancées : %d\n", $requestCount));
    }
    $lastReason = 'Erreur réseau inconnue';

    for ($attempt = 1; $attempt <= 3; ++$attempt) {
        try {
            $response = $client->request('GET', $url);
            $status = $response->getStatusCode();
            $html = $response->getContent(false);
            if ($status >= 200 && $status < 300) {
                usleep(REQUEST_DELAY_MICROSECONDS);
                return $html;
            }

            $lastReason = 'HTTP ' . $status;
            if ($status < 500 && $status !== 429) {
                break;
            }
        } catch (TransportExceptionInterface $exception) {
            $lastReason = get_class($exception);
        }

        usleep(REQUEST_DELAY_MICROSECONDS * $attempt);
    }

    throw new RuntimeException($lastReason);
};

$collectListing = static function (string $kind, string $route, callable $parse, array $query = []) use ($fetch, &$listingFailures): array {
    $entries = [];
    $firstUrl = SOURCE_BASE_URL . $route . ($query !== [] ? '?' . http_build_query($query) : '');
    $firstHtml = $fetch($firstUrl);
    $firstPage = $parse($firstHtml);
    $sourceCount = $firstPage['sourceCount'];
    if ($sourceCount === null || $firstPage['perPage'] === null) {
        throw new RuntimeException(sprintf('Pagination non reconnue pour %s.', $route));
    }
    $lastPage = $firstPage['lastPage'];
    array_push($entries, ...$firstPage['entries']);

    for ($page = 2; $page <= $lastPage; ++$page) {
        $pageQuery = $query;
        $pageQuery['page'] = $page;
        $url = SOURCE_BASE_URL . $route . '?' . http_build_query($pageQuery);
        try {
            $parsed = $parse($fetch($url));
            array_push($entries, ...$parsed['entries']);
        } catch (Throwable $exception) {
            $listingFailures[$kind][] = ['url' => $url, 'reason' => $exception->getMessage()];
        }
    }

    return ['sourceCount' => $sourceCount, 'lastPage' => $lastPage, 'entries' => $entries];
};

$lawyerListing = $collectListing('lawyers', '/annuaire', static fn (string $html): array => $parser->parseLawyerListing($html, SOURCE_BASE_URL));
$cabinetListing = $collectListing('cabinets', '/structures', static fn (string $html): array => $parser->parseCabinetListing($html, SOURCE_BASE_URL));

// The old directory exposes its two structure categories as explicit GET filters.
$cabinetTypes = [];
foreach (['cabinet' => 'Cabinet', 'SCPA' => 'SCPA'] as $typeValue => $typeLabel) {
    try {
        $typedListing = $collectListing('cabinets', '/structures', static fn (string $html): array => $parser->parseCabinetListing($html, SOURCE_BASE_URL), ['type' => $typeValue]);
        foreach ($typedListing['entries'] as $entry) {
            $cabinetTypes[$entry['sourceUuid']] = $typeLabel;
        }
    } catch (Throwable $exception) {
        $listingFailures['cabinets'][] = ['url' => SOURCE_BASE_URL . '/structures?type=' . rawurlencode($typeValue), 'reason' => $exception->getMessage()];
    }
}

$deduplicate = static function (array $entries, string $kind): array {
    $unique = [];
    $duplicates = [];
    foreach ($entries as $entry) {
        $uuid = $entry['sourceUuid'] ?? null;
        if (!is_string($uuid) || $uuid === '') {
            $duplicates[$kind . ':missing-uuid:' . count($duplicates)] = true;
            continue;
        }
        if (isset($unique[$uuid])) {
            $duplicates[$uuid] = true;
            continue;
        }
        $unique[$uuid] = $entry;
    }

    return ['entries' => array_values($unique), 'duplicateUuids' => array_keys($duplicates)];
};

$lawyerDedupe = $deduplicate($lawyerListing['entries'], 'lawyer');
$cabinetDedupe = $deduplicate($cabinetListing['entries'], 'cabinet');
$lawyers = [];
$consistencyIssues = [];

foreach ($lawyerDedupe['entries'] as $entry) {
    $detailUrl = $entry['sourceUrl'];
    try {
        $details = $parser->parseLawyerDetails($fetch($detailUrl), SOURCE_BASE_URL);
        foreach (['barNumber', 'professionalPhone', 'professionalEmail', 'sourceCabinetUuid', 'sourceCabinetName', 'locationRaw', 'latitude', 'longitude'] as $field) {
            if (($details[$field] ?? null) !== null && ($details[$field] ?? '') !== '') {
                if (in_array($field, ['sourceCabinetUuid', 'sourceCabinetName'], true)
                    && ($entry[$field] ?? null) !== null
                    && $entry[$field] !== $details[$field]
                ) {
                    $consistencyIssues[] = ['sourceUuid' => $entry['sourceUuid'], 'field' => $field, 'listingValue' => $entry[$field], 'detailValue' => $details[$field]];
                    continue;
                }
                $entry[$field] = $details[$field];
            }
        }
        $entry['detailStatus'] = 'OK';
    } catch (Throwable $exception) {
        $entry['detailStatus'] = 'FAILED';
        $entry['detailFailure'] = $exception->getMessage();
        $failures['lawyers'][] = ['sourceUuid' => $entry['sourceUuid'], 'url' => $detailUrl, 'reason' => $exception->getMessage()];
    }
    $lawyers[] = $entry;
}

$cabinets = [];
foreach ($cabinetDedupe['entries'] as $entry) {
    $detailUrl = $entry['sourceUrl'];
    $entry['typeRaw'] = $cabinetTypes[$entry['sourceUuid']] ?? null;
    try {
        $details = $parser->parseCabinetDetails($fetch($detailUrl), SOURCE_BASE_URL);
        foreach (['addressRaw', 'cityRaw', 'countryRaw', 'latitude', 'longitude', 'phones', 'email', 'website', 'description', 'memberSourceUuids'] as $field) {
            if (($details[$field] ?? null) !== null && ($details[$field] ?? []) !== []) {
                $entry[$field] = $details[$field];
            }
        }
        $entry['detailStatus'] = 'OK';
    } catch (Throwable $exception) {
        $entry['detailStatus'] = 'FAILED';
        $entry['detailFailure'] = $exception->getMessage();
        $failures['cabinets'][] = ['sourceUuid' => $entry['sourceUuid'], 'url' => $detailUrl, 'reason' => $exception->getMessage()];
    }
    $cabinets[] = $entry;
}

$lawyerIds = array_fill_keys(array_column($lawyers, 'sourceUuid'), true);
$cabinetIds = array_fill_keys(array_column($cabinets, 'sourceUuid'), true);
$nameGroups = [];
foreach ($lawyers as $lawyer) {
    $name = mb_strtolower(trim((string) ($lawyer['displayName'] ?? '')));
    if ($name !== '') {
        $nameGroups[$name][] = $lawyer['sourceUuid'];
    }
}
$duplicateNames = [];
foreach ($nameGroups as $name => $ids) {
    if (count($ids) > 1) {
        $duplicateNames[] = ['normalizedName' => $name, 'sourceUuids' => $ids];
    }
}

$unresolvedLawyerCabinets = [];
foreach ($lawyers as $lawyer) {
    $cabinetUuid = $lawyer['sourceCabinetUuid'] ?? null;
    if (is_string($cabinetUuid) && !isset($cabinetIds[$cabinetUuid])) {
        $unresolvedLawyerCabinets[] = ['lawyerSourceUuid' => $lawyer['sourceUuid'], 'cabinetSourceUuid' => $cabinetUuid, 'cabinetName' => $lawyer['sourceCabinetName'] ?? null];
    } elseif ($cabinetUuid === null && ($lawyer['sourceCabinetName'] ?? null) !== null) {
        $unresolvedLawyerCabinets[] = ['lawyerSourceUuid' => $lawyer['sourceUuid'], 'cabinetSourceUuid' => null, 'cabinetName' => $lawyer['sourceCabinetName']];
    }
}

$unmatchedCabinetMembers = [];
foreach ($cabinets as $cabinet) {
    foreach ($cabinet['memberSourceUuids'] as $memberUuid) {
        if (!isset($lawyerIds[$memberUuid])) {
            $unmatchedCabinetMembers[] = ['cabinetSourceUuid' => $cabinet['sourceUuid'], 'lawyerSourceUuid' => $memberUuid];
        }
    }
}

$sourceUuidToCabinetMembers = [];
foreach ($cabinets as $cabinet) {
    foreach ($cabinet['memberSourceUuids'] as $memberUuid) {
        $sourceUuidToCabinetMembers[$memberUuid][] = $cabinet['sourceUuid'];
    }
}
$relationMismatches = [];
foreach ($lawyers as $lawyer) {
    $cabinetUuid = $lawyer['sourceCabinetUuid'] ?? null;
    if (is_string($cabinetUuid) && isset($cabinetIds[$cabinetUuid])
        && !in_array($cabinetUuid, $sourceUuidToCabinetMembers[$lawyer['sourceUuid']] ?? [], true)
    ) {
        $relationMismatches[] = ['lawyerSourceUuid' => $lawyer['sourceUuid'], 'cabinetSourceUuid' => $cabinetUuid];
    }
}

$metadata = static fn (?int $sourceCount, int $successfulCount, int $failedCount): array => [
    'extractedAt' => (new DateTimeImmutable())->format(DATE_ATOM),
    'sourceBaseUrl' => SOURCE_BASE_URL,
    'sourceCount' => $sourceCount,
    'successfulCount' => $successfulCount,
    'failedCount' => $failedCount,
    'extractorVersion' => EXTRACTOR_VERSION,
];

$lawyersDocument = [
    'metadata' => $metadata($lawyerListing['sourceCount'], count($lawyers), count($failures['lawyers']) + count($listingFailures['lawyers'])),
    'entries' => $lawyers,
    'listingFailures' => $listingFailures['lawyers'],
    'detailFailures' => $failures['lawyers'],
];
$cabinetsDocument = [
    'metadata' => $metadata($cabinetListing['sourceCount'], count($cabinets), count($failures['cabinets']) + count($listingFailures['cabinets'])),
    'entries' => $cabinets,
    'listingFailures' => $listingFailures['cabinets'],
    'detailFailures' => $failures['cabinets'],
];

$detailSuccessLawyers = count(array_filter($lawyers, static fn (array $item): bool => ($item['detailStatus'] ?? null) === 'OK'));
$detailSuccessCabinets = count(array_filter($cabinets, static fn (array $item): bool => ($item['detailStatus'] ?? null) === 'OK'));
$quality = [
    'extractedAt' => (new DateTimeImmutable())->format(DATE_ATOM),
    'sourceBaseUrl' => SOURCE_BASE_URL,
    'extractorVersion' => EXTRACTOR_VERSION,
    'complete' => $lawyerListing['sourceCount'] === count($lawyers)
        && $cabinetListing['sourceCount'] === count($cabinets)
        && $detailSuccessLawyers === count($lawyers)
        && $detailSuccessCabinets === count($cabinets)
        && $listingFailures['lawyers'] === []
        && $listingFailures['cabinets'] === []
        && $lawyerDedupe['duplicateUuids'] === []
        && $cabinetDedupe['duplicateUuids'] === [],
    'lawyers' => [
        'listingCount' => $lawyerListing['sourceCount'],
        'listingPagesDetected' => $lawyerListing['lastPage'],
        'listingEntriesRead' => count($lawyerListing['entries']),
        'uniqueEntriesExported' => count($lawyers),
        'detailsSucceeded' => $detailSuccessLawyers,
        'detailsFailed' => count($failures['lawyers']),
        'withoutCabinet' => count(array_filter($lawyers, static fn (array $item): bool => ($item['sourceCabinetUuid'] ?? null) === null && ($item['sourceCabinetName'] ?? null) === null)),
        'withoutPortrait' => count(array_filter($lawyers, static fn (array $item): bool => ($item['portraitAvailable'] ?? false) !== true)),
        'withoutPhone' => count(array_filter($lawyers, static fn (array $item): bool => ($item['professionalPhone'] ?? null) === null)),
        'withoutEmail' => count(array_filter($lawyers, static fn (array $item): bool => ($item['professionalEmail'] ?? null) === null)),
        'withoutBarNumber' => count(array_filter($lawyers, static fn (array $item): bool => ($item['barNumber'] ?? null) === null)),
        'missingDisplayName' => count(array_filter($lawyers, static fn (array $item): bool => ($item['displayName'] ?? null) === null)),
        'duplicateSourceUuids' => $lawyerDedupe['duplicateUuids'],
        'possibleDuplicateNames' => $duplicateNames,
    ],
    'cabinets' => [
        'listingCount' => $cabinetListing['sourceCount'],
        'listingPagesDetected' => $cabinetListing['lastPage'],
        'listingEntriesRead' => count($cabinetListing['entries']),
        'uniqueEntriesExported' => count($cabinets),
        'detailsSucceeded' => $detailSuccessCabinets,
        'detailsFailed' => count($failures['cabinets']),
        'withoutMembers' => count(array_filter($cabinets, static fn (array $item): bool => ($item['memberSourceUuids'] ?? []) === [])),
        'withoutEmail' => count(array_filter($cabinets, static fn (array $item): bool => ($item['email'] ?? null) === null)),
        'withMultiplePhones' => count(array_filter($cabinets, static fn (array $item): bool => count($item['phones'] ?? []) > 1)),
        'withoutTypeRaw' => count(array_filter($cabinets, static fn (array $item): bool => ($item['typeRaw'] ?? null) === null)),
        'duplicateSourceUuids' => $cabinetDedupe['duplicateUuids'],
    ],
    'relations' => [
        'lawyerCabinetUnresolved' => $unresolvedLawyerCabinets,
        'cabinetMembersWithoutLawyer' => $unmatchedCabinetMembers,
        'lawyerCabinetMembershipMismatch' => $relationMismatches,
    ],
    'consistencyIssues' => $consistencyIssues,
    'listingFailures' => ['lawyers' => $listingFailures['lawyers'], 'cabinets' => $listingFailures['cabinets']],
    'detailFailures' => $failures,
];

$encode = static function (array $data): string {
    return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
};

if ($write) {
    if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0775, true) && !is_dir($outputDirectory)) {
        throw new RuntimeException('Impossible de créer le répertoire d’export.');
    }
    foreach (array_combine($outputFiles, [$encode($lawyersDocument), $encode($cabinetsDocument), $encode($quality)]) as $path => $contents) {
        if (is_file($path) && !$force) {
            throw new RuntimeException('Export existant : relancez avec --force pour le remplacer explicitement.');
        }
        $temporary = tempnam($outputDirectory, '.directory-export-');
        if ($temporary === false || file_put_contents($temporary, $contents, LOCK_EX) === false || !rename($temporary, $path)) {
            if (is_string($temporary) && is_file($temporary)) {
                unlink($temporary);
            }
            throw new RuntimeException(sprintf('Échec de l’écriture de %s.', basename($path)));
        }
    }
}

$summary = [
    'mode' => $write ? 'write' : 'dry-run',
    'lawyers' => ['announced' => $lawyerListing['sourceCount'], 'listed' => count($lawyerListing['entries']), 'exported' => count($lawyers), 'detailSuccess' => $detailSuccessLawyers, 'detailFailures' => count($failures['lawyers'])],
    'cabinets' => ['announced' => $cabinetListing['sourceCount'], 'listed' => count($cabinetListing['entries']), 'exported' => count($cabinets), 'detailSuccess' => $detailSuccessCabinets, 'detailFailures' => count($failures['cabinets'])],
    'complete' => $quality['complete'],
];
if ($write) {
    $summary['files'] = array_map(static fn (string $path): array => ['path' => $path, 'bytes' => filesize($path)], $outputFiles);
}
fwrite(STDOUT, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");

exit($quality['complete'] ? 0 : 1);

<?php

declare(strict_types=1);

namespace Websymphonie\Tools\OldDirectory;

use Throwable;

final class FailedCabinetDetailRetry
{
    public const MAX_ATTEMPTS = 3;
    public const RETRY_DELAY_MICROSECONDS = 150000;

    private const DETAIL_FIELDS = [
        'addressRaw',
        'cityRaw',
        'countryRaw',
        'latitude',
        'longitude',
        'phones',
        'email',
        'website',
        'description',
        'memberSourceUuids',
    ];

    public function __construct(private readonly LegacyDirectoryHtmlParser $parser)
    {
    }

    /**
     * @param array<string, mixed> $cabinet
     * @param callable(string): array{status: int, body: string} $request
     * @param callable(int): void|null $pause
     *
     * @return array{
     *     cabinet: array<string, mixed>,
     *     recovered: bool,
     *     attempts: int,
     *     observations: list<array{status: ?int, reason: ?string, errorBody: ?string}>,
     *     failureReason: ?string
     * }
     */
    public function retry(array $cabinet, callable $request, ?callable $pause = null): array
    {
        $pause ??= static function (int $microseconds): void {
            usleep($microseconds);
        };
        $observations = [];
        $failureReason = 'Erreur réseau inconnue';

        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; ++$attempt) {
            try {
                $response = $request((string) $cabinet['sourceUrl']);
                $status = $response['status'];
                $body = $response['body'];
                $errorBodyClassification = $status >= 500 ? $this->classifyErrorBody($body) : null;

                if ($status >= 200 && $status < 300) {
                    $details = $this->parser->parseCabinetDetails($body, 'https://app.ordredesavocats-ci.net');
                    foreach (self::DETAIL_FIELDS as $field) {
                        $value = $details[$field] ?? null;
                        if ($value !== null && $value !== []) {
                            $cabinet[$field] = $value;
                        }
                    }
                    $cabinet['detailStatus'] = 'OK';
                    $cabinet['detailAvailable'] = true;
                    $cabinet['detailRetryAttempts'] = $attempt;
                    unset($cabinet['detailFailure'], $cabinet['detailRetryObservations']);
                    $observations[] = ['status' => $status, 'reason' => null, 'errorBody' => null];

                    return [
                        'cabinet' => $cabinet,
                        'recovered' => true,
                        'attempts' => $attempt,
                        'observations' => $observations,
                        'failureReason' => null,
                    ];
                }

                $failureReason = 'HTTP ' . $status;
                $observations[] = ['status' => $status, 'reason' => $failureReason, 'errorBody' => $errorBodyClassification];

                if (($status < 500 && $status !== 429) || $attempt === self::MAX_ATTEMPTS) {
                    break;
                }
            } catch (Throwable $exception) {
                $failureReason = $exception->getMessage() !== '' ? $exception->getMessage() : get_class($exception);
                $observations[] = ['status' => null, 'reason' => get_class($exception), 'errorBody' => null];
                if ($attempt === self::MAX_ATTEMPTS) {
                    break;
                }
            }

            $pause(self::RETRY_DELAY_MICROSECONDS * $attempt);
        }

        $cabinet['detailStatus'] = 'FAILED';
        $cabinet['detailAvailable'] = false;
        $cabinet['detailRetryAttempts'] = count($observations);
        $cabinet['detailFailure'] = $failureReason;
        $cabinet['detailRetryObservations'] = $observations;

        return [
            'cabinet' => $cabinet,
            'recovered' => false,
            'attempts' => count($observations),
            'observations' => $observations,
            'failureReason' => $failureReason,
        ];
    }

    /**
     * Reconstructs membership only from exact source UUID relations in the lawyer export.
     *
     * @param array<string, mixed> $cabinet
     * @param list<array<string, mixed>> $lawyers
     *
     * @return array<string, mixed>
     */
    public function addLawyerExportMemberReferences(array $cabinet, array $lawyers): array
    {
        $cabinetUuid = $cabinet['sourceUuid'] ?? null;
        $memberUuidsFromLawyerExport = [];

        if (is_string($cabinetUuid)) {
            foreach ($lawyers as $lawyer) {
                if (($lawyer['sourceCabinetUuid'] ?? null) === $cabinetUuid
                    && is_string($lawyer['sourceUuid'] ?? null)
                    && !in_array($lawyer['sourceUuid'], $memberUuidsFromLawyerExport, true)
                ) {
                    $memberUuidsFromLawyerExport[] = $lawyer['sourceUuid'];
                }
            }
        }

        $listingMemberUuids = is_array($cabinet['memberSourceUuids'] ?? null) ? $cabinet['memberSourceUuids'] : [];
        $cabinet['memberSourceUuidsFromLawyerExport'] = $memberUuidsFromLawyerExport;
        $cabinet['memberSourceUuids'] = array_values(array_unique([...$listingMemberUuids, ...$memberUuidsFromLawyerExport]));

        return $cabinet;
    }

    private function classifyErrorBody(string $body): string
    {
        $text = trim(html_entity_decode(strip_tags(substr($body, 0, 20000)), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($text === '') {
            return 'empty';
        }

        if (preg_match('/stack trace|traceback|exception|SQLSTATE|Symfony\\\\Component|Laravel\\\\|Whoops!/i', $text) === 1) {
            return 'diagnostic_details_present';
        }

        if (preg_match('/internal server error|server error|erreur serveur|error 500|erreur 500/i', $text) === 1) {
            return 'generic_error_message';
        }

        return 'nonempty_unclassified_error_body';
    }
}

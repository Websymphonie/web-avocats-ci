<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Tools\OldDirectory;

use PHPUnit\Framework\TestCase;
use Websymphonie\Tools\OldDirectory\FailedCabinetDetailRetry;
use Websymphonie\Tools\OldDirectory\LegacyDirectoryHtmlParser;

require_once dirname(__DIR__, 3) . '/tools/data/old-directory/LegacyDirectoryHtmlParser.php';
require_once dirname(__DIR__, 3) . '/tools/data/old-directory/FailedCabinetDetailRetry.php';

final class LegacyDirectoryHtmlParserTest extends TestCase
{
    private LegacyDirectoryHtmlParser $parser;

    protected function setUp(): void
    {
        $this->parser = new LegacyDirectoryHtmlParser();
    }

    public function testParsesLawyerListingWithoutSplittingNamesAndKeepsMissingValuesNull(): void
    {
        $result = $this->parser->parseLawyerListing($this->fixture('lawyer-listing.html'), 'https://app.ordredesavocats-ci.net');

        self::assertSame(605, $result['sourceCount']);
        self::assertSame(51, $result['lastPage']);
        self::assertSame(12, $result['perPage']);
        self::assertCount(2, $result['entries']);
        self::assertSame('Maître Élodie N’Guessan-Kouassi', $result['entries'][0]['displayName']);
        self::assertSame('elodie@example.test', $result['entries'][0]['professionalEmail']);
        self::assertSame('07 00 00 01', $result['entries'][0]['professionalPhone']);
        self::assertSame('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', $result['entries'][0]['sourceCabinetUuid']);
        self::assertTrue($result['entries'][0]['portraitAvailable']);
        self::assertNull($result['entries'][1]['professionalEmail']);
        self::assertNull($result['entries'][1]['professionalPhone']);
        self::assertNull($result['entries'][1]['sourceCabinetUuid']);
        self::assertNull($result['entries'][1]['portraitSourceUrl']);
    }

    public function testParsesLawyerDetailsAndDoesNotUseColleagueContactDetails(): void
    {
        $result = $this->parser->parseLawyerDetails($this->fixture('lawyer-detail.html'), 'https://app.ordredesavocats-ci.net');

        self::assertSame('24/001', $result['barNumber']);
        self::assertSame('elodie@example.test', $result['professionalEmail']);
        self::assertSame('07 00 00 01', $result['professionalPhone']);
        self::assertSame('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', $result['sourceCabinetUuid']);
        self::assertSame("5.3609, -3.9958", $result['locationRaw']);
        self::assertSame('5.3609', $result['latitude']);
        self::assertSame('-3.9958', $result['longitude']);
    }

    public function testParsesCabinetListingMemberUuids(): void
    {
        $result = $this->parser->parseCabinetListing($this->fixture('cabinet-listing.html'), 'https://app.ordredesavocats-ci.net');

        self::assertSame(377, $result['sourceCount']);
        self::assertSame("SCPA L'IVOIRE - Associés", $result['entries'][0]['name']);
        self::assertSame([
            '11111111-1111-4111-8111-111111111111',
            '22222222-2222-4222-8222-222222222222',
        ], $result['entries'][0]['memberSourceUuids']);
    }

    public function testParsesCabinetMultiplePhonesAddressCoordinatesAndMembers(): void
    {
        $result = $this->parser->parseCabinetDetails($this->fixture('cabinet-detail.html'), 'https://app.ordredesavocats-ci.net');

        self::assertSame(['27 22 00 00 01', '01 00 00 02'], $result['phones']);
        self::assertSame('contact@example.test', $result['email']);
        self::assertSame('Cocody, Abidjan', $result['addressRaw']);
        self::assertSame('5.3', $result['latitude']);
        self::assertSame('-4.0', $result['longitude']);
        self::assertSame([
            '11111111-1111-4111-8111-111111111111',
            '22222222-2222-4222-8222-222222222222',
        ], $result['memberSourceUuids']);
    }

    public function testTargetedCabinetRetryRecoversAfterTemporaryServerError(): void
    {
        $retry = new FailedCabinetDetailRetry($this->parser);
        $responses = [
            ['status' => 500, 'body' => '<html><title>Internal Server Error</title></html>'],
            ['status' => 200, 'body' => $this->fixture('cabinet-detail.html')],
        ];
        $calls = 0;

        $result = $retry->retry($this->failedCabinetListingEntry(), static function (string $url) use (&$calls, &$responses): array {
            ++$calls;

            return array_shift($responses);
        }, static function (int $delay): void {
        });

        self::assertTrue($result['recovered']);
        self::assertSame(2, $calls);
        self::assertSame(2, $result['attempts']);
        self::assertSame('OK', $result['cabinet']['detailStatus']);
        self::assertTrue($result['cabinet']['detailAvailable']);
        self::assertSame('Listing Cabinet', $result['cabinet']['name']);
        self::assertSame('contact@example.test', $result['cabinet']['email']);
        self::assertSame('Cocody, Abidjan', $result['cabinet']['addressRaw']);
    }

    public function testPersistentCabinetFailureRetainsOnlyListingFallbackData(): void
    {
        $retry = new FailedCabinetDetailRetry($this->parser);
        $calls = 0;

        $result = $retry->retry($this->failedCabinetListingEntry(), static function (string $url) use (&$calls): array {
            ++$calls;

            return ['status' => 500, 'body' => '<html><title>Internal Server Error</title><body>Server error</body></html>'];
        }, static function (int $delay): void {
        });

        self::assertFalse($result['recovered']);
        self::assertSame(3, $calls);
        self::assertSame(3, $result['attempts']);
        self::assertSame('FAILED', $result['cabinet']['detailStatus']);
        self::assertFalse($result['cabinet']['detailAvailable']);
        self::assertSame('Listing Cabinet', $result['cabinet']['name']);
        self::assertSame('Cabinet', $result['cabinet']['typeRaw']);
        self::assertSame('listing@example.test', $result['cabinet']['email']);
        self::assertSame([], $result['cabinet']['phones']);
        self::assertArrayNotHasKey('addressRaw', $result['cabinet']);
        self::assertSame('generic_error_message', $result['observations'][0]['errorBody']);
    }

    public function testFailedCabinetMembersAreReconstructedOnlyFromExactSourceUuid(): void
    {
        $retry = new FailedCabinetDetailRetry($this->parser);
        $cabinet = $this->failedCabinetListingEntry();
        $cabinet['sourceUuid'] = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
        $cabinet['memberSourceUuids'] = ['bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'];
        $lawyers = [
            ['sourceUuid' => '11111111-1111-4111-8111-111111111111', 'sourceCabinetUuid' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'],
            ['sourceUuid' => '22222222-2222-4222-8222-222222222222', 'sourceCabinetUuid' => 'cccccccc-cccc-4ccc-8ccc-cccccccccccc', 'sourceCabinetName' => 'Listing Cabinet'],
        ];

        $result = $retry->addLawyerExportMemberReferences($cabinet, $lawyers);

        self::assertSame([
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
            '11111111-1111-4111-8111-111111111111',
        ], $result['memberSourceUuids']);
        self::assertSame(['11111111-1111-4111-8111-111111111111'], $result['memberSourceUuidsFromLawyerExport']);
    }

    public function testMissingUuidRelationDoesNotFabricateMemberFromSimilarName(): void
    {
        $retry = new FailedCabinetDetailRetry($this->parser);
        $cabinet = $this->failedCabinetListingEntry();
        $cabinet['sourceUuid'] = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
        $lawyers = [[
            'sourceUuid' => '11111111-1111-4111-8111-111111111111',
            'sourceCabinetUuid' => null,
            'sourceCabinetName' => 'Listing Cabinet',
        ]];

        $result = $retry->addLawyerExportMemberReferences($cabinet, $lawyers);

        self::assertSame([], $result['memberSourceUuids']);
        self::assertSame([], $result['memberSourceUuidsFromLawyerExport']);
    }

    /** @return array<string, mixed> */
    private function failedCabinetListingEntry(): array
    {
        return [
            'sourceUuid' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'sourceUrl' => 'https://app.ordredesavocats-ci.net/structures/aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'name' => 'Listing Cabinet',
            'typeRaw' => 'Cabinet',
            'email' => 'listing@example.test',
            'phones' => [],
            'memberSourceUuids' => [],
            'detailStatus' => 'FAILED',
            'detailFailure' => 'HTTP 500',
        ];
    }

    private function fixture(string $filename): string
    {
        $contents = file_get_contents(dirname(__DIR__, 2) . '/Fixtures/OldDirectory/' . $filename);
        self::assertNotFalse($contents);

        return $contents;
    }
}

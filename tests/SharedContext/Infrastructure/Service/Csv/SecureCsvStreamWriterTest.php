<?php

declare(strict_types=1);

namespace Websymphonie\Tests\SharedContext\Infrastructure\Service\Csv;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Websymphonie\SharedContext\Application\Service\Csv\CsvCell;
use Websymphonie\SharedContext\Application\Service\Csv\CsvDownloadDefinition;
use Websymphonie\SharedContext\Infrastructure\Service\Csv\SecureCsvStreamWriter;

final class SecureCsvStreamWriterTest extends TestCase
{
    public function testItStreamsTheFixedCsvFormatAndEscapesValues(): void
    {
        $definition = new CsvDownloadDefinition(
            'finance-2026.csv',
            ['Nom', 'Montant (FCFA)', 'Date', 'Note'],
            [
                [
                    CsvCell::text('Électricité; agence'),
                    CsvCell::number(100000),
                    CsvCell::date(new DateTimeImmutable('2026-09-13 12:00:00')),
                    CsvCell::text("Ligne 1\nLigne \"2\""),
                ],
                [CsvCell::text(null), CsvCell::number(0), CsvCell::date(null), CsvCell::text(null)],
            ],
        );

        $response = (new SecureCsvStreamWriter())->response($definition);

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        self::assertIsString($content);
        self::assertStringStartsWith("\xEF\xBB\xBF", $content);
        self::assertSame(
            "\xEF\xBB\xBFNom;\"Montant (FCFA)\";Date;Note\r\n"
            . "\"Électricité; agence\";100000;2026-09-13;\"Ligne 1\nLigne \"\"2\"\"\"\r\n"
            . ";0;;\r\n",
            $content,
        );
    }

    /** @dataProvider formulaValues */
    public function testItNeutralizesFormulaLikeTextOnly(string $input, string $expected): void
    {
        $definition = new CsvDownloadDefinition('safe.csv', ['Valeur'], [[CsvCell::text($input)]]);
        $response = (new SecureCsvStreamWriter())->response($definition);

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        self::assertSame(['Valeur'], str_getcsv(explode("\r\n", substr((string) $content, 3))[0], ';', '"', ''));
        self::assertSame([$expected], str_getcsv(explode("\r\n", substr((string) $content, 3))[1], ';', '"', ''));
    }

    /** @return iterable<string, array{string, string}> */
    public static function formulaValues(): iterable
    {
        yield 'equals' => ['=SUM(A1:A2)', "'=SUM(A1:A2)"];
        yield 'plus' => ['+cmd', "'+cmd"];
        yield 'at' => ['@value', "'@value"];
        yield 'minus' => ['-formula', "'-formula"];
        yield 'leading whitespace' => [" \t=hidden", "' \t=hidden"];
        yield 'normal text' => ['normal text', 'normal text'];
    }

    public function testItKeepsNumericSignsAndZero(): void
    {
        $definition = new CsvDownloadDefinition(
            'numbers.csv',
            ['A', 'B', 'C'],
            [[CsvCell::number(100000), CsvCell::number(0), CsvCell::number(-5000)]],
        );
        $response = (new SecureCsvStreamWriter())->response($definition);

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        self::assertSame("\xEF\xBB\xBFA;B;C\r\n100000;0;-5000\r\n", $content);
    }

    public function testItSetsPrivateDownloadHeaders(): void
    {
        $response = (new SecureCsvStreamWriter())->response(new CsvDownloadDefinition('safe.csv', ['A'], []));

        self::assertSame('text/csv; charset=UTF-8', $response->headers->get('Content-Type'));
        self::assertSame('attachment; filename=safe.csv', $response->headers->get('Content-Disposition'));
        self::assertSame('no-store, private', $response->headers->get('Cache-Control'));
        self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    public function testRowsAreNotIteratedBeforeContentIsSent(): void
    {
        $started = false;
        $rows = (function () use (&$started): iterable {
            $started = true;
            yield [CsvCell::text('lazy')];
        })();

        $response = (new SecureCsvStreamWriter())->response(new CsvDownloadDefinition('lazy.csv', ['A'], $rows));

        self::assertFalse($started);
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        self::assertTrue($started);
        self::assertStringContainsString("lazy\r\n", (string) $content);
    }

    /** @dataProvider unsafeFilenames */
    public function testItRejectsUnsafeFilenames(string $filename): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CsvDownloadDefinition($filename, ['A'], []);
    }

    /** @return iterable<string, array{string}> */
    public static function unsafeFilenames(): iterable
    {
        yield 'empty' => [''];
        yield 'path traversal' => ['../evil.csv'];
        yield 'absolute path' => ['/tmp/evil.csv'];
        yield 'header injection' => ["evil\r\n.csv"];
        yield 'dot' => ['.'];
        yield 'dot dot' => ['..'];
    }
}

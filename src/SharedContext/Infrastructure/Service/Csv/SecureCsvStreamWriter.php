<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Service\Csv;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Websymphonie\SharedContext\Application\Service\Csv\CsvCell;
use Websymphonie\SharedContext\Application\Service\Csv\CsvDownloadDefinition;
use Websymphonie\SharedContext\Application\Service\Csv\CsvStreamWriterInterface;

final readonly class SecureCsvStreamWriter implements CsvStreamWriterInterface
{
    public function response(CsvDownloadDefinition $definition): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($definition): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                throw new RuntimeException('Unable to open the CSV output stream.');
            }

            if (fwrite($handle, "\xEF\xBB\xBF") !== 3) {
                fclose($handle);
                throw new RuntimeException('Unable to write the CSV UTF-8 BOM.');
            }
            $this->writeValues($handle, $definition->headers);

            foreach ($definition->rows as $row) {
                $this->writeRow($handle, $row);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $definition->filename),
        );
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }

    /** @param resource $handle */
    private function writeRow(mixed $handle, mixed $row): void
    {
        if (!is_array($row)) {
            throw new InvalidArgumentException('Each CSV row must be an array of CsvCell values.');
        }

        $values = [];

        foreach ($row as $cell) {
            if (!$cell instanceof CsvCell) {
                throw new InvalidArgumentException('Each CSV cell must be created with CsvCell.');
            }

            $values[] = $cell->render();
        }

        $this->writeValues($handle, $values);
    }

    /**
     * @param resource $handle
     * @param array<int, string> $values
     */
    private function writeValues(mixed $handle, array $values): void
    {
        if (fputcsv($handle, $values, ';', '"', '', "\r\n") === false) {
            throw new RuntimeException('Unable to write a CSV row.');
        }
    }
}

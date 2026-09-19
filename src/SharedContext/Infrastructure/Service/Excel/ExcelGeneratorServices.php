<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Service\Excel;

use PhpOffice\PhpSpreadsheet\Reader\Html;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Websymphonie\SharedContext\Application\Service\Excel\ExcelDefinition;
use Websymphonie\SharedContext\Application\Service\Excel\ExcelInterfaces;
use Websymphonie\SharedContext\Infrastructure\Service\Excel\Factory\ExcelGeneratorFactory;

readonly class ExcelGeneratorServices implements ExcelInterfaces
{
    public function __construct(
        private Environment           $twig,
        private ExcelGeneratorFactory $factory,
    )
    {
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function generate(ExcelDefinition $excel): Response
    {

        $filename = "{$excel->title()}.{$excel->format()->extension()}";

        // 1. Rendu du template Twig
        $htmlString = $this->twig->render($excel->template(), $excel->templateVariables());

        // 2. Lecture HTML vers Spreadsheet
        $reader = new Html();
        $spreadsheet = $reader->loadFromString($htmlString);
        $sheet = $spreadsheet->getActiveSheet();

        // 3. Application des styles
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // a) Bordures sur toutes les cellules
        $sheet->getStyle("A1:$highestColumn$highestRow")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->setColor(new Color('000000'));

        // b) Style des en-têtes (ligne 1)
        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => '000000'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FFD9D9D9'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ]
            ]
        ]);

        // 4. Génération du writer
        $writer = $this->factory->fromExcelFormat($excel->format(), $spreadsheet);

        // 5. Envoi en téléchargement
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', $excel->format()->contentType());
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename));
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}

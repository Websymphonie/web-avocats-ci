<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Service\Pdf;

use Nucleos\DompdfBundle\Factory\DompdfFactoryInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Websymphonie\SharedContext\Application\Service\Pdf\PdfDefinition;
use Websymphonie\SharedContext\Application\Service\Pdf\PdfInterface;

readonly class PdfGeneratorService implements PdfInterface
{
    public function __construct(private DompdfFactoryInterface $factory, private Environment $environment)
    {
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function generate(PdfDefinition $pdf): string
    {
        $dompdf = $this->factory->create();
        $template = $this->environment->render($pdf->template(), $pdf->templateVariables());
        $template = str_replace(
            ["\u{00A0}", "\u{202F}", "\xc2\xa0", "\xe2\x80\xaf"],
            ' ',
            $template
        );
        $dompdf->loadHtml(mb_convert_encoding($template, 'UTF-8', 'auto'));
        $dompdf->setPaper($pdf->format()->value);
        $dompdf->render();
        return $dompdf->output();
    }
}

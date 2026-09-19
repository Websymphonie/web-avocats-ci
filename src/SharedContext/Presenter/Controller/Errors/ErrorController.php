<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Controller\Errors;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final readonly class ErrorController
{
    public function __construct(private Environment $twig)
    {
    }

    public function index(FlattenException $exception): Response
    {
        $statusCode = $exception->getStatusCode();
        $view = sprintf('bundles/TwigBundle/Exception/error%d.html.twig', $statusCode);

        if (!$this->twig->getLoader()->exists($view)) {
            $view = 'bundles/TwigBundle/Exception/error.html.twig';
        }

        return new Response($this->twig->render($view, [
            'statusCode' => $statusCode,
            'statusText' => Response::$statusTexts[$statusCode] ?? 'Erreur inattendue',
        ]), $statusCode);
    }
}

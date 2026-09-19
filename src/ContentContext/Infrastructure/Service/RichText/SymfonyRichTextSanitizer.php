<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Service\RichText;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;

final readonly class SymfonyRichTextSanitizer implements RichTextSanitizerInterface
{
    public function __construct(private HtmlSanitizerInterface $sanitizer) {}

    public static function config(): HtmlSanitizerConfig
    {
        return (new HtmlSanitizerConfig())
            ->allowElement('p')
            ->allowElement('br')
            ->allowElement('h2')
            ->allowElement('h3')
            ->allowElement('strong')
            ->allowElement('em')
            ->allowElement('u')
            ->allowElement('s')
            ->allowElement('ul')
            ->allowElement('ol')
            ->allowElement('li')
            ->allowElement('blockquote')
            ->allowElement('hr')
            ->allowElement('a', ['href', 'title', 'target'])
            ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
            ->allowRelativeLinks()
            ->forceAttribute('a', 'rel', 'noopener noreferrer');
    }

    public function sanitize(string $html): string
    {
        return $this->sanitizer->sanitize($html);
    }
}

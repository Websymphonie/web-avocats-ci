<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Unit\ContentContext\Infrastructure\Service\RichText;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Websymphonie\ContentContext\Infrastructure\Service\RichText\SymfonyRichTextSanitizer;

final class SymfonyRichTextSanitizerTest extends TestCase
{
    public function testItKeepsTheEditorialWhitelistAndRemovesUnsafeMarkup(): void
    {
        $sanitizer = new SymfonyRichTextSanitizer(new HtmlSanitizer(SymfonyRichTextSanitizer::config()));

        $result = $sanitizer->sanitize(<<<'HTML'
            <h2>Actualité</h2>
            <p><strong>Important</strong> <em>message</em></p>
            <ul><li>Point sûr</li></ul>
            <blockquote>Citation</blockquote>
            <p><a href="https://example.com" target="_blank">Lien sûr</a> <a href="javascript:alert(1)">Lien dangereux</a></p>
            <script>alert(1)</script><iframe src="https://evil.example"></iframe><img src="x" onerror="alert(1)">
            HTML);

        self::assertStringContainsString('<h2>Actualité</h2>', $result);
        self::assertStringContainsString('<strong>Important</strong>', $result);
        self::assertStringContainsString('<blockquote>Citation</blockquote>', $result);
        self::assertStringContainsString('href="https://example.com"', $result);
        self::assertStringContainsString('rel="noopener noreferrer"', $result);
        self::assertStringNotContainsString('<script', $result);
        self::assertStringNotContainsString('<iframe', $result);
        self::assertStringNotContainsString('<img', $result);
        self::assertStringNotContainsString('javascript:', $result);
    }

    public function testItRemovesUnsupportedStylesAndEventHandlers(): void
    {
        $sanitizer = new SymfonyRichTextSanitizer(new HtmlSanitizer(SymfonyRichTextSanitizer::config()));

        $result = $sanitizer->sanitize('<p style="color:red" onclick="alert(1)">Texte</p><h1>Interdit</h1>');

        self::assertSame('<p>Texte</p>', $result);
        self::assertStringNotContainsString('style=', $result);
        self::assertStringNotContainsString('onclick=', $result);
    }

    public function testEmptyContentRemainsEmpty(): void
    {
        $sanitizer = new SymfonyRichTextSanitizer(new HtmlSanitizer(SymfonyRichTextSanitizer::config()));

        self::assertSame('', $sanitizer->sanitize(''));
    }
}

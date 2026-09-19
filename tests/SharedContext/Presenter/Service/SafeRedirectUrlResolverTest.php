<?php

declare(strict_types=1);

namespace Websymphonie\Tests\SharedContext\Presenter\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Websymphonie\SharedContext\Presenter\Service\SafeRedirectUrlResolver;

final class SafeRedirectUrlResolverTest extends TestCase
{
    /**
     * @dataProvider unsafeRedirects
     */
    public function testExternalRedirectsUseFallback(string $candidate): void
    {
        $request = Request::create('/', 'GET', [], [], [], ['HTTP_HOST' => 'app.test']);

        self::assertSame(
            '/fallback',
            SafeRedirectUrlResolver::resolve($request, '/fallback', $candidate)
        );
    }

    public static function unsafeRedirects(): iterable
    {
        yield 'external absolute URL' => ['https://evil.example/account'];
        yield 'protocol relative URL' => ['//evil.example/account'];
        yield 'non URL value' => ['javascript:alert(1)'];
    }

    public function testInternalRedirectIsPreserved(): void
    {
        $request = Request::create('/', 'GET', [], [], [], ['HTTP_HOST' => 'app.test']);

        self::assertSame(
            '/properties?status=available',
            SafeRedirectUrlResolver::resolve(
                $request,
                '/fallback',
                'http://app.test/properties?status=available'
            )
        );
    }
}

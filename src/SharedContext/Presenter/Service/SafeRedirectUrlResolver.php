<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Service;

use Symfony\Component\HttpFoundation\Request;

final class SafeRedirectUrlResolver
{
    public static function resolve(Request $request, string $fallback, ?string $candidate = null): string
    {
        $candidate ??= $request->headers->get('referer');
        if ($candidate === null || trim($candidate) === '') {
            return $fallback;
        }

        $parts = parse_url($candidate);
        if ($parts === false || isset($parts['user'], $parts['pass'])) {
            return $fallback;
        }

        if (isset($parts['host'])) {
            if (strcasecmp((string) $parts['host'], $request->getHost()) !== 0) {
                return $fallback;
            }

            $scheme = isset($parts['scheme']) ? strtolower((string) $parts['scheme']) : null;
            if ($scheme !== null && $scheme !== strtolower($request->getScheme())) {
                return $fallback;
            }

            if (isset($parts['port']) && (int) $parts['port'] !== $request->getPort()) {
                return $fallback;
            }
        }

        $path = (string) ($parts['path'] ?? '');
        if ($path === '' || !str_starts_with($path, '/') || str_starts_with($path, '//') || str_contains($path, '\\')) {
            return $fallback;
        }

        return $path . (isset($parts['query']) ? '?' . $parts['query'] : '');
    }
}

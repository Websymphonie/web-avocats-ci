<?php

declare(strict_types=1);

namespace Websymphonie\Tools\OldDirectory;

use Symfony\Component\DomCrawler\Crawler;

final class LegacyDirectoryHtmlParser
{
    private const LAWYER_UUID_PATTERN = '~\/avocat\/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})(?:$|[?#])~i';
    private const CABINET_UUID_PATTERN = '~\/structures\/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})(?:$|[?#])~i';

    /** @return array{sourceCount: ?int, perPage: ?int, lastPage: int, entries: list<array<string, mixed>>} */
    public function parseLawyerListing(string $html, string $baseUrl): array
    {
        $crawler = new Crawler($html);
        $entries = [];

        $crawler->filter('div.card.border-t-blue')->each(function (Crawler $card) use (&$entries, $baseUrl): void {
            $profileLinks = $card->filter('a[href]')->reduce(fn (Crawler $link): bool => $this->extractUuid($link->attr('href') ?? '', self::LAWYER_UUID_PATTERN) !== null);
            if ($profileLinks->count() === 0) {
                return;
            }

            $profileLink = $profileLinks->first();
            $href = $this->absoluteUrl($profileLink->attr('href') ?? '', $baseUrl);
            $uuid = $this->extractUuid($href, self::LAWYER_UUID_PATTERN);
            if ($uuid === null) {
                return;
            }

            $displayName = $this->cleanText($profileLink->text(''));

            $cabinetLink = $card->filter('a[href]')->reduce(fn (Crawler $link): bool => str_contains($link->attr('href') ?? '', '/structures/'));
            $cabinetUrl = $cabinetLink->count() > 0 ? $this->absoluteUrl($cabinetLink->first()->attr('href') ?? '', $baseUrl) : null;
            $cabinetNameNode = $cabinetLink->count() > 0 ? $cabinetLink->first()->filter('[data-bs-title]') : new Crawler();
            $cabinetName = $cabinetNameNode->count() > 0
                ? $this->cleanText($cabinetNameNode->first()->attr('data-bs-title') ?? '')
                : ($cabinetLink->count() > 0 ? $this->cleanText($cabinetLink->first()->text('')) : null);

            $portrait = $card->filter('img[src]')->reduce(fn (Crawler $image): bool => str_contains($image->attr('src') ?? '', '/storage/Profile/'));
            $email = $this->firstMailto($card);
            $phone = $this->firstPhone($card);

            $entries[] = [
                'sourceUuid' => $uuid,
                'displayName' => $displayName !== '' ? $displayName : null,
                'barNumber' => null,
                'professionalPhone' => $phone,
                'professionalEmail' => $email,
                'sourceCabinetUuid' => $cabinetUrl !== null ? $this->extractUuid($cabinetUrl, self::CABINET_UUID_PATTERN) : null,
                'sourceCabinetName' => $cabinetName,
                'locationRaw' => null,
                'latitude' => null,
                'longitude' => null,
                'portraitSourceUrl' => $portrait->count() > 0 ? $this->absoluteUrl($portrait->first()->attr('src') ?? '', $baseUrl) : null,
                'portraitAvailable' => $portrait->count() > 0,
                'sourceWasPublic' => true,
                'sourceUrl' => $href,
            ];
        });

        return $this->withPagination($crawler, $entries);
    }

    /** @return array{sourceCount: ?int, perPage: ?int, lastPage: int, entries: list<array<string, mixed>>} */
    public function parseCabinetListing(string $html, string $baseUrl): array
    {
        $crawler = new Crawler($html);
        $entries = [];

        $crawler->filter('div.card.px-0.pb-0')->each(function (Crawler $card) use (&$entries, $baseUrl): void {
            $cabinetLinks = $card->filter('a[href]')->reduce(fn (Crawler $link): bool => $this->extractUuid($link->attr('href') ?? '', self::CABINET_UUID_PATTERN) !== null);
            if ($cabinetLinks->count() === 0) {
                return;
            }

            $cabinetLink = $cabinetLinks->first();
            $href = $this->absoluteUrl($cabinetLink->attr('href') ?? '', $baseUrl);
            $uuid = $this->extractUuid($href, self::CABINET_UUID_PATTERN);
            if ($uuid === null) {
                return;
            }

            $nameNode = $cabinetLink->filter('h6');
            $name = $nameNode->count() > 0 ? $this->cleanText($nameNode->first()->text('')) : $this->cleanText($cabinetLink->text(''));
            $members = $this->extractUuids($card, self::LAWYER_UUID_PATTERN, $baseUrl);

            $entries[] = [
                'sourceUuid' => $uuid,
                'name' => $name !== '' ? $name : null,
                'typeRaw' => null,
                'addressRaw' => null,
                'cityRaw' => null,
                'countryRaw' => null,
                'latitude' => null,
                'longitude' => null,
                'phones' => [],
                'email' => $this->firstMailto($card),
                'website' => null,
                'description' => null,
                'memberSourceUuids' => $members,
                'sourceUrl' => $href,
            ];
        });

        return $this->withPagination($crawler, $entries);
    }

    /** @return array{barNumber: ?string, professionalPhone: ?string, professionalEmail: ?string, sourceCabinetUuid: ?string, sourceCabinetName: ?string, locationRaw: ?string, latitude: ?string, longitude: ?string} */
    public function parseLawyerDetails(string $html, string $baseUrl): array
    {
        $crawler = new Crawler($html);
        $profile = $crawler->filter('.hovercard .info');
        if ($profile->count() === 0) {
            throw new \UnexpectedValueException('Fiche avocat inattendue : bloc de profil absent.');
        }
        $profile = $profile->first();
        $barNumber = null;
        $profile->filter('.ttl-info')->each(function (Crawler $node) use (&$barNumber): void {
            $heading = $node->filter('h6');
            if ($heading->count() === 0 || !str_contains(mb_strtolower($this->cleanText($heading->text(''))), 'toge')) {
                return;
            }

            $value = $node->filter('span[title]');
            $barNumber = $this->cleanText($value->count() > 0 ? ($value->first()->attr('title') ?? '') : $node->text('')) ?: null;
        });

        $cabinetLink = $profile->filter('a[href]')->reduce(fn (Crawler $link): bool => str_contains($link->attr('href') ?? '', '/structures/'));
        $cabinetUrl = $cabinetLink->count() > 0 ? $this->absoluteUrl($cabinetLink->first()->attr('href') ?? '', $baseUrl) : null;
        $cabinetName = $cabinetLink->count() > 0
            ? $this->cleanText($cabinetLink->first()->text(''))
            : null;
        [$locationRaw, $latitude, $longitude] = $this->extractLocation($profile);

        return [
            'barNumber' => $barNumber,
            'professionalPhone' => $this->firstPhone($profile),
            'professionalEmail' => $this->firstMailto($profile),
            'sourceCabinetUuid' => $cabinetUrl !== null ? $this->extractUuid($cabinetUrl, self::CABINET_UUID_PATTERN) : null,
            'sourceCabinetName' => $cabinetName !== '' ? $cabinetName : null,
            'locationRaw' => $locationRaw,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }

    /** @return array{typeRaw: ?string, addressRaw: ?string, cityRaw: ?string, countryRaw: ?string, latitude: ?string, longitude: ?string, phones: list<string>, email: ?string, website: ?string, description: ?string, memberSourceUuids: list<string>} */
    public function parseCabinetDetails(string $html, string $baseUrl): array
    {
        $crawler = new Crawler($html);
        $sidebar = $crawler->filter('#structure_show .email-app-sidebar');
        if ($sidebar->count() === 0) {
            throw new \UnexpectedValueException('Fiche cabinet inattendue : bloc de coordonnées absent.');
        } else {
            $sidebar = $sidebar->first();
        }

        $phones = [];
        $sidebar->filter('a[href^="tel:"]')->each(function (Crawler $link) use (&$phones): void {
            $phone = $this->phoneFromHref($link->attr('href') ?? '');
            if ($phone !== null && !in_array($phone, $phones, true)) {
                $phones[] = $phone;
            }
        });

        $addressRaw = null;
        $mapLink = $sidebar->filter('a[href*="maps.google"]');
        if ($mapLink->count() > 0) {
            $addressRaw = $this->cleanText($mapLink->first()->closest('li')->attr('title') ?? '');
            if ($addressRaw === '') {
                $addressRaw = null;
            }
        }

        [$locationRaw, $latitude, $longitude] = $this->extractLocation($sidebar);
        $memberUuids = $this->extractUuids($crawler, self::LAWYER_UUID_PATTERN, $baseUrl);

        return [
            'typeRaw' => null,
            'addressRaw' => $addressRaw,
            'cityRaw' => null,
            'countryRaw' => null,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'phones' => $phones,
            'email' => $this->firstMailto($sidebar),
            'website' => $this->labelledUrl($sidebar, ['site web', 'website', 'site internet'], $baseUrl),
            'description' => $this->labelledText($sidebar, ['description', 'présentation']),
            'memberSourceUuids' => $memberUuids,
        ];
    }

    /** @param list<array<string, mixed>> $entries
     *  @return array{sourceCount: ?int, perPage: ?int, lastPage: int, entries: list<array<string, mixed>>}
     */
    private function withPagination(Crawler $crawler, array $entries): array
    {
        $sourceCount = null;
        $perPage = null;
        $summary = $crawler->filter('p.small.text-muted');
        if ($summary->count() > 0 && preg_match('/Showing\s+([\d,]+)\s+to\s+([\d,]+)\s+of\s+([\d,]+)\s+results/i', $this->cleanText($summary->first()->text('')), $matches) === 1) {
            $first = (int) str_replace(',', '', $matches[1]);
            $last = (int) str_replace(',', '', $matches[2]);
            $sourceCount = (int) str_replace(',', '', $matches[3]);
            $perPage = max(1, $last - $first + 1);
        }

        $lastPage = 1;
        $crawler->filter('.pagination a[href]')->each(function (Crawler $link) use (&$lastPage): void {
            $query = parse_url($link->attr('href') ?? '', PHP_URL_QUERY);
            if (!is_string($query)) {
                return;
            }

            parse_str($query, $parameters);
            if (isset($parameters['page']) && is_numeric($parameters['page'])) {
                $lastPage = max($lastPage, (int) $parameters['page']);
            }
        });

        if ($sourceCount !== null) {
            $lastPage = max($lastPage, (int) ceil($sourceCount / $perPage));
        }

        return ['sourceCount' => $sourceCount, 'perPage' => $perPage, 'lastPage' => $lastPage, 'entries' => $entries];
    }

    /** @return list<string> */
    private function extractUuids(Crawler $crawler, string $pattern, string $baseUrl): array
    {
        $uuids = [];
        $crawler->filter('a[href]')->each(function (Crawler $link) use (&$uuids, $pattern, $baseUrl): void {
            $url = $this->absoluteUrl($link->attr('href') ?? '', $baseUrl);
            $uuid = $this->extractUuid($url, $pattern);
            if ($uuid !== null && !in_array($uuid, $uuids, true)) {
                $uuids[] = $uuid;
            }
        });

        return $uuids;
    }

    /** @return array{?string, ?string, ?string} */
    private function extractLocation(Crawler $crawler): array
    {
        $locationRaw = null;
        $crawler->filter('[data-position]')->each(function (Crawler $node) use (&$locationRaw): void {
            if ($locationRaw === null) {
                $locationRaw = $this->cleanText($node->attr('data-position') ?? '');
            }
        });

        if ($locationRaw === null) {
            $maps = $crawler->filter('a[href*="maps.google"]');
            if ($maps->count() > 0) {
                $locationRaw = $this->cleanText($maps->first()->attr('href') ?? '');
            }
        }

        if ($locationRaw === null || preg_match('/(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)/', rawurldecode($locationRaw), $matches) !== 1) {
            return [$locationRaw !== '' ? $locationRaw : null, null, null];
        }

        return [$locationRaw, $matches[1], $matches[2]];
    }

    private function firstMailto(Crawler $crawler): ?string
    {
        $email = null;
        $crawler->filter('a[href^="mailto:"]')->each(function (Crawler $link) use (&$email): void {
            if ($email !== null) {
                return;
            }
            $email = trim(rawurldecode(substr($link->attr('href') ?? '', 7))) ?: null;
        });

        return $email;
    }

    private function firstPhone(Crawler $crawler): ?string
    {
        $phone = null;
        $crawler->filter('a[href^="tel:"]')->each(function (Crawler $link) use (&$phone): void {
            $phone ??= $this->phoneFromHref($link->attr('href') ?? '');
        });

        return $phone;
    }

    private function phoneFromHref(string $href): ?string
    {
        $phone = trim(rawurldecode(substr($href, 4)));

        return $phone !== '' ? $phone : null;
    }

    /** @param list<string> $labels */
    private function labelledUrl(Crawler $crawler, array $labels, string $baseUrl): ?string
    {
        $url = null;
        $crawler->filter('a[href]')->each(function (Crawler $link) use ($labels, $baseUrl, &$url): void {
            if ($url !== null) {
                return;
            }
            $text = mb_strtolower($this->cleanText($link->text('')));
            foreach ($labels as $label) {
                if (str_contains($text, $label)) {
                    $url = $this->absoluteUrl($link->attr('href') ?? '', $baseUrl);
                    return;
                }
            }
        });

        return $url;
    }

    /** @param list<string> $labels */
    private function labelledText(Crawler $crawler, array $labels): ?string
    {
        $value = null;
        $crawler->filter('p, li, dd, div')->each(function (Crawler $node) use ($labels, &$value): void {
            if ($value !== null) {
                return;
            }
            $text = $this->cleanText($node->text(''));
            $lower = mb_strtolower($text);
            foreach ($labels as $label) {
                if (str_starts_with($lower, $label . ':')) {
                    $candidate = trim(substr($text, strlen($label) + 1));
                    $value = $candidate !== '' ? $candidate : null;
                    return;
                }
            }
        });

        return $value;
    }

    private function extractUuid(string $url, string $pattern): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || preg_match($pattern, $path, $matches) !== 1) {
            return null;
        }

        return mb_strtolower($matches[1]);
    }

    private function absoluteUrl(string $url, string $baseUrl): string
    {
        $url = trim(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }
        if (preg_match('#^https?://#i', $url) === 1) {
            return $url;
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
    }

    private function cleanText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace(["\xc2\xa0", "\xe2\x80\x8b"], [' ', ''], $text);

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}

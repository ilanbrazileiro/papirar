<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;

class MarketingAttribution
{
    public static function applyToUser(Request $request, User $user): void
    {
        $first = (array) $request->session()->get('marketing.first_touch', []);
        $last = (array) $request->session()->get('marketing.last_touch', $first);

        $source = self::source($last, $first);

        $user->forceFill([
            'acquisition_source' => $source,
            'acquisition_medium' => self::pick($last, $first, 'utm_medium'),
            'acquisition_campaign' => self::pick($last, $first, 'utm_campaign'),
            'acquisition_content' => self::pick($last, $first, 'utm_content'),
            'acquisition_term' => self::pick($last, $first, 'utm_term'),
            'acquisition_landing_path' => self::pick($first, $last, 'landing_path'),
            'acquisition_referrer' => self::pick($first, $last, 'referrer'),
            'acquisition_captured_at' => now(),
        ])->save();
    }

    private static function source(array $last, array $first): string
    {
        $utmSource = self::pick($last, $first, 'utm_source');

        if ($utmSource) {
            return $utmSource;
        }

        $landing = (string) self::pick($first, $last, 'landing_path');

        if (str_starts_with($landing, '/questoes/')) {
            return 'public_question';
        }

        if (str_starts_with($landing, '/cursos/')) {
            return 'public_course';
        }

        $referrer = (string) self::pick($first, $last, 'referrer');

        if ($referrer) {
            $host = parse_url($referrer, PHP_URL_HOST);

            if ($host) {
                return mb_substr((string) $host, 0, 100);
            }
        }

        return 'direct';
    }

    private static function pick(array $primary, array $fallback, string $key): ?string
    {
        $value = $primary[$key] ?? $fallback[$key] ?? null;

        return is_string($value) && trim($value) !== ''
            ? trim($value)
            : null;
    }
}

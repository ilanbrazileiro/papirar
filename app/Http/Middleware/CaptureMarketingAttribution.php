<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureMarketingAttribution
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && !$request->expectsJson()) {
            $session = $request->session();

            $utm = [
                'utm_source' => $this->clean($request->query('utm_source')),
                'utm_medium' => $this->clean($request->query('utm_medium')),
                'utm_campaign' => $this->clean($request->query('utm_campaign')),
                'utm_content' => $this->clean($request->query('utm_content')),
                'utm_term' => $this->clean($request->query('utm_term')),
            ];

            $hasUtm = collect($utm)->filter()->isNotEmpty();

            if (!$session->has('marketing.first_touch')) {
                $session->put('marketing.first_touch', array_merge($utm, [
                    'landing_path' => '/' . ltrim($request->path(), '/'),
                    'referrer' => $this->clean($request->headers->get('referer'), 1000),
                    'captured_at' => now()->toIso8601String(),
                ]));
            }

            if ($hasUtm) {
                $session->put('marketing.last_touch', array_merge($utm, [
                    'landing_path' => '/' . ltrim($request->path(), '/'),
                    'referrer' => $this->clean($request->headers->get('referer'), 1000),
                    'captured_at' => now()->toIso8601String(),
                ]));
            } elseif (!$session->has('marketing.last_touch')) {
                $session->put('marketing.last_touch', $session->get('marketing.first_touch'));
            }
        }

        return $next($request);
    }

    private function clean(?string $value, int $max = 255): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim(strip_tags($value));

        return $value !== '' ? mb_substr($value, 0, $max) : null;
    }
}

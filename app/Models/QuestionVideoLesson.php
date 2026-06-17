<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class QuestionVideoLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'title',
        'provider',
        'video_url',
        'embed_url',
        'thumbnail_url',
        'duration_seconds',
        'visibility',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'question_id' => 'integer',
        'duration_seconds' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function providerLabel(): string
    {
        return match ($this->provider) {
            'youtube' => 'YouTube',
            'vimeo' => 'Vimeo',
            'external' => 'Link externo',
            'html' => 'Embed HTML',
            default => ucfirst((string) $this->provider),
        };
    }

    public function visibilityLabel(): string
    {
        return match ($this->visibility) {
            'public' => 'Pública',
            'course_access' => 'Somente alunos com acesso ao curso',
            default => ucfirst((string) $this->visibility),
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'active' => 'Ativa',
            'inactive' => 'Inativa',
            default => ucfirst((string) $this->status),
        };
    }

    public function resolvedEmbedUrl(): ?string
    {
        if ($this->embed_url) {
            return $this->embed_url;
        }

        if (!$this->video_url) {
            return null;
        }

        return self::makeEmbedUrl($this->video_url, $this->provider);
    }

    public static function makeEmbedUrl(?string $url, ?string $provider): ?string
    {
        if (!$url) {
            return null;
        }

        $provider = $provider ?: self::detectProvider($url);

        if ($provider === 'youtube') {
            $id = self::extractYoutubeId($url);
            return $id ? "https://www.youtube.com/embed/{$id}" : null;
        }

        if ($provider === 'vimeo') {
            $id = self::extractVimeoId($url);
            return $id ? "https://player.vimeo.com/video/{$id}" : null;
        }

        return null;
    }

    public static function detectProvider(?string $url): string
    {
        $url = (string) $url;

        if (Str::contains($url, ['youtube.com', 'youtu.be'])) {
            return 'youtube';
        }

        if (Str::contains($url, ['vimeo.com'])) {
            return 'vimeo';
        }

        return 'external';
    }

    public static function extractYoutubeId(string $url): ?string
    {
        $parts = parse_url($url);
        $host = $parts['host'] ?? '';
        $path = trim($parts['path'] ?? '', '/');

        if (Str::contains($host, 'youtu.be') && $path !== '') {
            return Str::before($path, '?');
        }

        if (Str::contains($host, 'youtube.com')) {
            parse_str($parts['query'] ?? '', $query);
            if (!empty($query['v'])) {
                return $query['v'];
            }

            if (Str::startsWith($path, 'embed/')) {
                return Str::after($path, 'embed/');
            }

            if (Str::startsWith($path, 'shorts/')) {
                return Str::after($path, 'shorts/');
            }
        }

        return null;
    }

    public static function extractVimeoId(string $url): ?string
    {
        $path = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');
        $segments = array_values(array_filter(explode('/', $path)));

        foreach (array_reverse($segments) as $segment) {
            if (ctype_digit($segment)) {
                return $segment;
            }
        }

        return null;
    }

    public function formattedDuration(): ?string
    {
        if (!$this->duration_seconds) {
            return null;
        }

        $minutes = intdiv($this->duration_seconds, 60);
        $seconds = $this->duration_seconds % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}

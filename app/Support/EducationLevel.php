<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

final class EducationLevel
{
    public const MEDIUM = 'medium';
    public const HIGHER = 'higher';

    public const LABELS = [
        self::MEDIUM => 'Médio',
        self::HIGHER => 'Superior',
    ];

    public static function options(): array
    {
        return self::LABELS;
    }

    public static function isValid(?string $level): bool
    {
        return array_key_exists((string) $level, self::LABELS);
    }

    /**
     * Restricts a question query using the independently configured course level.
     * Unclassified questions are excluded; an unclassified course receives no questions.
     */
    public static function restrictQuestions(Builder $query, ?string $courseLevel): Builder
    {
        return match ($courseLevel) {
            self::MEDIUM => $query->where('questions.education_level', self::MEDIUM),
            self::HIGHER => $query->whereIn('questions.education_level', [self::MEDIUM, self::HIGHER]),
            default => $query->whereRaw('1 = 0'),
        };
    }
}

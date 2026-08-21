<?php

namespace App\Support;

use App\Models\Question;
use Illuminate\Support\Str;

class PublicQuestionUrl
{
    public static function subjectSlug(Question $question): string
    {
        return $question->subject?->slug
            ?: Str::slug($question->subject?->name ?: 'questoes');
    }

    public static function questionSlug(Question $question): string
    {
        $plain = html_entity_decode(
            (string) $question->statement,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $plain = strip_tags($plain);
        $plain = preg_replace('/\s+/u', ' ', $plain) ?: '';

        $slug = Str::slug(Str::limit(trim($plain), 90, ''));

        return $slug !== '' ? $slug : 'questao';
    }

    public static function url(Question $question): string
    {
        return route('site.questions.show', [
            'subjectSlug' => self::subjectSlug($question),
            'question' => $question->id,
            'questionSlug' => self::questionSlug($question),
        ]);
    }
}

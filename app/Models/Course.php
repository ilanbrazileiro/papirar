<?php

namespace App\Models;

use InvalidArgumentException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Course extends Model
{
    use HasFactory;

    public const TYPE_INTERNAL_EXAM = 'internal_exam';
    public const TYPE_EXTERNAL_EXAM = 'external_exam';
    public const TYPE_SUBJECT_COURSE = 'subject_course';
    public const TYPE_TOPIC_COURSE = 'topic_course';
    public const TYPE_COMBO = 'combo';

    public const BILLING_MONTHLY = 'monthly';
    public const BILLING_QUARTERLY = 'quarterly';
    public const BILLING_SEMIANNUAL = 'semiannual';

    protected $fillable = [
        'corporation_id',
        'exam_id',
        'title',
        'slug',
        'short_description',
        'description',
        'cover_image_path',
        'sales_headline',
        'sales_badge',
        'sales_bullets',
        'target_audience',
        'workload_label',
        'guarantee_text',
        'course_type',
        'price',
        'quarterly_price',
        'semiannual_price',
        'inherit_exam_scope',
        'active',
        'is_public',
        'is_trial_available',
        'trial_days',
        'sort_order',
    ];

    protected $casts = [
        'corporation_id' => 'integer',
        'exam_id' => 'integer',
        'sales_bullets' => 'array',
        'price' => 'decimal:2',
        'quarterly_price' => 'decimal:2',
        'semiannual_price' => 'decimal:2',
        'inherit_exam_scope' => 'boolean',
        'active' => 'boolean',
        'is_public' => 'boolean',
        'is_trial_available' => 'boolean',
        'trial_days' => 'integer',
        'sort_order' => 'integer',
    ];

    public static function typeOptions(): array
    {
        return [
            self::TYPE_INTERNAL_EXAM => 'Concurso interno',
            self::TYPE_EXTERNAL_EXAM => 'Concurso de ingresso',
            self::TYPE_SUBJECT_COURSE => 'Curso por disciplina',
            self::TYPE_TOPIC_COURSE => 'Curso por tópico',
            self::TYPE_COMBO => 'Combo',
        ];
    }

    public static function billingCycleOptions(): array
    {
        return [
            self::BILLING_MONTHLY => 'Mensal',
            self::BILLING_QUARTERLY => 'Trimestral',
            self::BILLING_SEMIANNUAL => 'Semestral',
        ];
    }

    public function corporation(): BelongsTo
    {
        return $this->belongsTo(Corporation::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function courseSubjects(): HasMany
    {
        return $this->hasMany(CourseSubject::class)->orderBy('sort_order');
    }

    public function courseTopics(): HasMany
    {
        return $this->hasMany(CourseTopic::class)->orderBy('sort_order');
    }

    public function courseSourceMaterials(): HasMany
    {
        return $this->hasMany(CourseSourceMaterial::class)->orderBy('sort_order');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'course_subjects')
            ->withPivot(['sort_order', 'is_active'])
            ->withTimestamps()
            ->wherePivot('is_active', true)
            ->orderBy('course_subjects.sort_order')
            ->orderBy('subjects.name');
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class, 'course_topics')
            ->withPivot(['sort_order', 'is_active'])
            ->withTimestamps()
            ->wherePivot('is_active', true)
            ->orderBy('course_topics.sort_order')
            ->orderBy('topics.name');
    }

    public function sourceMaterials(): BelongsToMany
    {
        return $this->belongsToMany(SourceMaterial::class, 'course_source_materials')
            ->withPivot(['sort_order', 'is_active'])
            ->withTimestamps()
            ->wherePivot('is_active', true)
            ->orderBy('course_source_materials.sort_order')
            ->orderBy('source_materials.title');
    }

    public function bundleItems(): HasMany
    {
        return $this->hasMany(CourseBundleItem::class, 'bundle_course_id');
    }

    public function includedCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_bundle_items', 'bundle_course_id', 'included_course_id')
            ->withTimestamps()
            ->orderBy('courses.title');
    }

    public function includedInBundles(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_bundle_items', 'included_course_id', 'bundle_course_id')
            ->withTimestamps()
            ->orderBy('courses.title');
    }

    public function accesses(): HasMany
    {
        return $this->hasMany(CourseAccess::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeTrialAvailable($query)
    {
        return $query->where('is_trial_available', true)
            ->where('trial_days', '>', 0);
    }

    public function typeLabel(): string
    {
        return self::typeOptions()[$this->course_type] ?? $this->course_type;
    }

    public function billingCycleLabel(string $billingCycle): string
    {
        return self::billingCycleOptions()[$billingCycle] ?? $billingCycle;
    }

    public function priceForBillingCycle(string $billingCycle): float
    {
        return match ($billingCycle) {
            self::BILLING_MONTHLY => (float) $this->price,
            self::BILLING_QUARTERLY => (float) ($this->quarterly_price ?: 0),
            self::BILLING_SEMIANNUAL => (float) ($this->semiannual_price ?: 0),
            default => throw new InvalidArgumentException('Ciclo de cobrança inválido.'),
        };
    }

    public function periodDaysForBillingCycle(string $billingCycle): int
    {
        return match ($billingCycle) {
            self::BILLING_MONTHLY => 30,
            self::BILLING_QUARTERLY => 90,
            self::BILLING_SEMIANNUAL => 180,
            default => throw new InvalidArgumentException('Ciclo de cobrança inválido.'),
        };
    }

    public function availableBillingCycles(): array
    {
        return collect(self::billingCycleOptions())
            ->filter(fn ($label, $cycle) => $this->priceForBillingCycle((string) $cycle) > 0)
            ->all();
    }

    public function hasQuarterlyPrice(): bool
    {
        return $this->quarterly_price !== null && (float) $this->quarterly_price > 0;
    }

    public function hasSemiannualPrice(): bool
    {
        return $this->semiannual_price !== null && (float) $this->semiannual_price > 0;
    }

    public function trialDaysForAccess(): int
    {
        $days = (int) ($this->trial_days ?: 7);

        return max(1, min($days, 30));
    }

    public function coverImageUrl(): ?string
    {
        if (!$this->cover_image_path) {
            return null;
        }

        if (str_starts_with($this->cover_image_path, 'http://') || str_starts_with($this->cover_image_path, 'https://')) {
            return $this->cover_image_path;
        }

        return Storage::disk('public')->url($this->cover_image_path);
    }

    public function salesBulletsList(): array
    {
        $bullets = $this->sales_bullets;

        if (is_string($bullets)) {
            $decoded = json_decode($bullets, true);
            $bullets = is_array($decoded) ? $decoded : [];
        }

        return collect($bullets ?: [])
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }

    public function salesBulletsText(): string
    {
        return implode("\n", $this->salesBulletsList());
    }

    public function commercialHeadline(): string
    {
        return $this->sales_headline ?: ($this->short_description ?: 'Curso direcionado para sua preparação.');
    }

    public function bestCommercialPriceLabel(): string
    {
        if ($this->hasSemiannualPrice()) {
            return 'Semestral por R$ ' . number_format((float) $this->semiannual_price, 2, ',', '.');
        }

        if ($this->hasQuarterlyPrice()) {
            return 'Trimestral por R$ ' . number_format((float) $this->quarterly_price, 2, ',', '.');
        }

        if ((float) $this->price > 0) {
            return 'Mensal por R$ ' . number_format((float) $this->price, 2, ',', '.');
        }

        return 'Preço indisponível';
    }
}

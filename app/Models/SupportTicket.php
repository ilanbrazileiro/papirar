<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'category',
        'status',
        'priority',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class, 'ticket_id');
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'suggestion' => 'Sugestão',
            'technical' => 'Problema técnico',
            'financial' => 'Problema financeiro',
            'question_submission' => 'Envio de questões',
            default => 'Atendimento',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Aberto',
            'in_progress' => 'Em andamento',
            'resolved' => 'Resolvido',
            'closed' => 'Fechado',
            default => ucfirst((string) $this->status),
        };
    }
}

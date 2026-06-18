<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SupportTicketMessageAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_ticket_message_id',
        'original_name',
        'file_path',
        'mime_type',
        'extension',
        'file_size',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(SupportTicketMessage::class, 'support_ticket_message_id');
    }

    public function getPublicUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }
}

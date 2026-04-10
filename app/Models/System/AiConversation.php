<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AiConversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql';

    protected $table = 'ai_conversations';

    protected $fillable = [
        'session_id',
        'tenant_id',
        'user_type',
        'user_id',
        'portal',
        'question',
        'answer',
        'was_answered',
        'was_helpful',
        'flagged_for_review',
        'openai_tokens_used',
        'response_time_ms',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'metadata'           => 'array',
        'was_answered'       => 'boolean',
        'was_helpful'        => 'boolean',
        'flagged_for_review' => 'boolean',
    ];

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function unansweredQuestion(): HasOne
    {
        return $this->hasOne(AiUnansweredQuestion::class, 'conversation_id');
    }

    public function scopeForPortal(Builder $query, string $portal): Builder
    {
        return $query->where('portal', $portal);
    }

    public function scopeUnanswered(Builder $query): Builder
    {
        return $query->where('was_answered', false);
    }

    public function scopeFlagged(Builder $query): Builder
    {
        return $query->where('flagged_for_review', true);
    }
}

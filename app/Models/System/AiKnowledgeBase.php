<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiKnowledgeBase extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql';

    protected $table = 'ai_knowledge_base';

    protected $fillable = [
        'category',
        'question',
        'answer',
        'keywords',
        'portal_context',
        'language',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'keywords'       => 'array',
        'portal_context' => 'array',
        'is_active'      => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForPortal(Builder $query, string $portal): Builder
    {
        return $query->where(function (Builder $q) use ($portal) {
            $q->whereNull('portal_context')
              ->orWhereJsonContains('portal_context', $portal);
        });
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        $term = '%' . $search . '%';
        return $query->where(function (Builder $q) use ($term, $search) {
            $q->where('question', 'like', $term)
              ->orWhere('answer', 'like', $term)
              ->orWhereJsonContains('keywords', $search);
        });
    }
}

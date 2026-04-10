<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * CMS content entry managed exclusively by Super Admin.
 * Controls all guest/public page content.
 *
 * @property int $id
 * @property string $section
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property int $sort_order
 * @property bool $is_active
 */
class CmsContent extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $table = 'cms_content';

    protected $fillable = [
        'section',
        'key',
        'value',
        'type',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active'  => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope to only active content entries.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to a specific section.
     */
    public function scopeSection($query, string $section)
    {
        return $query->where('section', $section);
    }
}

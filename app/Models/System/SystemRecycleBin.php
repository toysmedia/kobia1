<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * System-level recycle bin entry — mirrors soft-deleted records from all tenants.
 * Allows Super Admin to restore or permanently purge any deleted record.
 *
 * @property int $id
 * @property int|null $tenant_id
 * @property string $model_type
 * @property int $model_id
 * @property array $data
 * @property string|null $deleted_by_type
 * @property int|null $deleted_by_id
 * @property \Carbon\Carbon $deleted_at
 * @property \Carbon\Carbon $created_at
 */
class SystemRecycleBin extends Model
{
    public const UPDATED_AT = null;

    protected $connection = 'mysql';

    protected $table = 'system_recycle_bin';

    protected $fillable = [
        'tenant_id',
        'model_type',
        'model_id',
        'data',
        'deleted_by_type',
        'deleted_by_id',
        'deleted_at',
    ];

    protected $casts = [
        'data'       => 'array',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The tenant this recycle bin entry belongs to.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}

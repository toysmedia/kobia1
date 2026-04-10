<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * Tenant-level recycle bin entry.
 * Stores soft-deleted records allowing the ISP admin to restore data.
 *
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property array $data
 * @property int|null $deleted_by
 * @property \Carbon\Carbon $deleted_at
 * @property \Carbon\Carbon $created_at
 */
class TenantRecycleBin extends Model
{
    public const UPDATED_AT = null;

    protected $connection = 'tenant';

    protected $table = 'tenant_recycle_bin';

    protected $fillable = [
        'model_type',
        'model_id',
        'data',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'data'       => 'array',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}

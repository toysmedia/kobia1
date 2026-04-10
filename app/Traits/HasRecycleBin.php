<?php

namespace App\Traits;

use App\Models\System\SystemRecycleBin;
use App\Models\Tenant\TenantRecycleBin;
use Illuminate\Support\Facades\Auth;

/**
 * HasRecycleBin — mirrors every soft-delete to both the tenant-level and
 * system-level recycle bins so data can be recovered from either portal.
 *
 * Usage: add `use SoftDeletes, HasRecycleBin;` to any model.
 * The SoftDeletes trait must also be applied.
 */
trait HasRecycleBin
{
    // -------------------------------------------------------------------------
    // Boot
    // -------------------------------------------------------------------------

    /**
     * Register model event listeners for the recycle bin behaviour.
     */
    protected static function bootHasRecycleBin(): void
    {
        static::deleted(function ($model) {
            if ($model->isForceDeleting()) {
                return;
            }

            $model->mirrorToRecycleBins();
        });
    }

    // -------------------------------------------------------------------------
    // Core methods
    // -------------------------------------------------------------------------

    /**
     * Write the deleted record to both recycle bin tables.
     */
    public function mirrorToRecycleBins(): void
    {
        $modelType   = get_class($this);
        $modelId     = $this->getKey();
        $data        = $this->toArray();
        $deletedBy   = Auth::id();
        $deletedByType = Auth::user() ? get_class(Auth::user()) : null;
        $deletedAt   = now();

        // Tenant-level recycle bin (runs on the tenant DB connection)
        try {
            TenantRecycleBin::on('tenant')->create([
                'model_type' => $modelType,
                'model_id'   => $modelId,
                'data'       => $data,
                'deleted_by' => $deletedBy,
                'deleted_at' => $deletedAt,
            ]);
        } catch (\Throwable) {
            // Tenant DB may not be connected yet during seeding/testing — skip gracefully
        }

        // System-level recycle bin (runs on the system DB connection)
        try {
            $tenantId = app()->bound('current_tenant')
                ? optional(app('current_tenant'))->id
                : null;

            SystemRecycleBin::on('mysql')->create([
                'tenant_id'       => $tenantId,
                'model_type'      => $modelType,
                'model_id'        => $modelId,
                'data'            => $data,
                'deleted_by_type' => $deletedByType,
                'deleted_by_id'   => $deletedBy,
                'deleted_at'      => $deletedAt,
            ]);
        } catch (\Throwable) {
            // System DB write failure should not prevent the original delete
        }
    }

    // -------------------------------------------------------------------------
    // Restore
    // -------------------------------------------------------------------------

    /**
     * Restore the model from a recycle bin entry.
     * Removes the bin entry after successful restoration.
     */
    public function restoreFromBin(): bool
    {
        if ($this->restore()) {
            TenantRecycleBin::on('tenant')
                ->where('model_type', get_class($this))
                ->where('model_id', $this->getKey())
                ->delete();

            return true;
        }

        return false;
    }

    // -------------------------------------------------------------------------
    // Force delete from bin
    // -------------------------------------------------------------------------

    /**
     * Permanently delete the model and remove it from all recycle bins.
     */
    public function forceDeleteFromBin(): bool
    {
        $modelType = get_class($this);
        $modelId   = $this->getKey();

        $this->forceDelete();

        try {
            TenantRecycleBin::on('tenant')
                ->where('model_type', $modelType)
                ->where('model_id', $modelId)
                ->delete();
        } catch (\Throwable) {
        }

        try {
            SystemRecycleBin::on('mysql')
                ->where('model_type', $modelType)
                ->where('model_id', $modelId)
                ->delete();
        } catch (\Throwable) {
        }

        return true;
    }
}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use SoftDeletes;
    protected $table = 'audit_logs';
    protected $guarded = ['id'];
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public static function record(string $action, string $model = null, int $modelId = null, array $old = [], array $new = []): void
    {
        self::create([
            'user_type' => auth()->guard('admin')->check() ? 'admin' : (auth()->check() ? 'user' : null),
            'user_id' => auth()->guard('admin')->id() ?? auth()->id(),
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}

<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Tenant-specific configuration setting stored in the tenant's database.
 * Sensitive settings (credentials, keys) are AES-256 encrypted at rest.
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $group
 * @property bool $is_encrypted
 */
class TenantSetting extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'tenant_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
        'is_encrypted',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Accessors / Mutators
    // -------------------------------------------------------------------------

    /**
     * Decrypt the value when reading if the setting is marked as encrypted.
     */
    public function getValueAttribute(?string $value): ?string
    {
        if ($this->is_encrypted && $value !== null) {
            return Crypt::decryptString($value);
        }

        return $value;
    }

    // -------------------------------------------------------------------------
    // Static helpers
    // -------------------------------------------------------------------------

    /**
     * Retrieve a setting value by key, with an optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set (upsert) a setting value.
     */
    public static function set(string $key, mixed $value, string $group = 'general', bool $encrypt = false): self
    {
        $storedValue = $encrypt ? Crypt::encryptString((string) $value) : (string) $value;

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $storedValue, 'group' => $group, 'is_encrypted' => $encrypt],
        );
    }
}

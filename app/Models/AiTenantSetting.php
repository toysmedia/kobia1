<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiTenantSetting extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'ai_tenant_settings';

    protected $fillable = [
        'ai_enabled',
        'customer_portal_ai_enabled',
        'custom_greeting',
        'custom_knowledge',
        'openai_usage_limit',
        'tokens_used_this_month',
    ];

    protected $casts = [
        'ai_enabled'                   => 'boolean',
        'customer_portal_ai_enabled'   => 'boolean',
        'custom_knowledge'             => 'array',
    ];
}

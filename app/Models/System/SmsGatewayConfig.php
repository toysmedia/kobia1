<?php
namespace App\Models\System;
use Illuminate\Database\Eloquent\Model;
class SmsGatewayConfig extends Model
{
    protected $connection = 'mysql';
    protected $table = 'sms_gateway_configs';
    protected $fillable = ['key', 'value', 'provider', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}

<?php
namespace App\Models\System;
use Illuminate\Database\Eloquent\Model;
class EmailGatewayConfig extends Model
{
    protected $connection = 'mysql';
    protected $table = 'email_gateway_configs';
    protected $fillable = ['key', 'value', 'driver', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}

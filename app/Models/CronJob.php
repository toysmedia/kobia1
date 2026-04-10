<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CronJob extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    public static function getAll(string $order_by = 'desc')
    {
        return self::orderBy('id', $order_by)->get();
    }
}

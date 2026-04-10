<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Radcheck extends Model
{
    protected $table = 'radcheck';
    public $timestamps = false;
    protected $guarded = ['id'];
}

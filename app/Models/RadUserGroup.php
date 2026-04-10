<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadUserGroup extends Model
{
    protected $table = 'radusergroup';
    public $timestamps = false;
    protected $guarded = ['id'];
}

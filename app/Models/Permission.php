<?php
namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'display_name', 'group', 'description'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }
}

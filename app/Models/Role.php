<?php
namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'display_name', 'description'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    public function admins()
    {
        return $this->belongsToMany(Admin::class, 'role_user', 'role_id', 'user_id')
                    ->wherePivot('user_type', 'admin');
    }
}

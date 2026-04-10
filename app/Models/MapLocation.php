<?php
namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class MapLocation extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name', 'type', 'latitude', 'longitude', 'description',
        'locatable_type', 'locatable_id', 'icon', 'metadata',
    ];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
        'metadata'  => 'array',
    ];

    public function locatable()
    {
        return $this->morphTo();
    }
}

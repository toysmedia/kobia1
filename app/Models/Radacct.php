<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Radacct extends Model
{
    protected $table = 'radacct';
    protected $primaryKey = 'radacctid';
    public $timestamps = false;
    protected $guarded = ['radacctid'];
    protected $casts = [
        'acctstarttime' => 'datetime',
        'acctstoptime' => 'datetime',
        'acctupdatetime' => 'datetime',
    ];

    public function isActive(): bool
    {
        return $this->acctstoptime === null;
    }
}

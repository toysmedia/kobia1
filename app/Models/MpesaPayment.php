<?php
namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MpesaPayment extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'mpesa_payments';
    protected $guarded = ['id'];
    protected $casts = [
        'raw_callback' => 'array',
        'transaction_date' => 'datetime',
    ];

    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function package()
    {
        return $this->belongsTo(IspPackage::class, 'isp_package_id');
    }
}

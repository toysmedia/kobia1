<?php
namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class MessageLog extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'type', 'recipient', 'subject', 'message', 'status',
        'gateway', 'subscriber_id', 'cost', 'response', 'sent_at',
    ];

    protected $casts = [
        'response' => 'array',
        'sent_at'  => 'datetime',
        'cost'     => 'decimal:4',
    ];

    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class);
    }
}

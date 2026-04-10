<?php
namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'title', 'description', 'amount', 'category', 'expense_category_id',
        'payment_method', 'reference', 'date', 'receipt_image', 'created_by',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];

    public function expenseCategory()
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}

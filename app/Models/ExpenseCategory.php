<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_active'];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function expense()
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    public function expensesByFilter($filter='month')
    {
        $query = $this->expense();

        switch ($filter) {
            case 'day':
                $query->whereDate('date', today());
                break;
            case 'week':
                $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereYear('date', now()->year)
                    ->whereMonth('date', now()->month);
                break;
            case 'year':
                $query->whereYear('date', now()->year);
                break;
            default:
                // No filter, return all expenses for this category
        }

        return $query->get();
    }
}

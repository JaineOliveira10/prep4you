<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyClosureClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'closure_id',
        'client_id',
        'total_simple_labels',
        'total_kit_labels',
        'total_superkit_labels',
        'unit_price_simple',
        'unit_price_kit',
        'total_simple_value',
        'total_kit_value',
        'total_superkit_value',
        'total_gross',
        'total_discount',
        'total_net',
        'created_at',
        'updated_at',
        'total_simple_net',
        'total_kit_net',
        'total_discount_simple',
        'total_discount_kit',
        'paid_flag'
    ];

    public function monthlyClosure()
    {
        return $this->belongsTo(MonthlyClosure::class, 'closure_id');
    }

}

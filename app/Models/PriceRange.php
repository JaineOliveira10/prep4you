<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceRange extends Model
{
    use HasFactory;

    protected $fillable = [
        'price_table_id',
        'min_value',
        'max_value',
        'price',
        'price_kit',
    ];

    public function priceTable()
    {
        return $this->belongsTo(PriceTable::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceRange extends Model
{
    use HasFactory, SoftDeletes;

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
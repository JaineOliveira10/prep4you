<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'asin', 
        'fsnku', 
        'sku', 
        'photo_path', 
        'observation', 
        'type', 
        'kit_units', 
        'unit_price', 
        'client_id'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}

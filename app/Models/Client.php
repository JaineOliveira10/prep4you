<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'city',
        'uf',
        'phone',
        'email',
        'price_table_id',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'client_id');
    }

    public function priceTable()
    {
        return $this->belongsTo(PriceTable::class);
    }
}

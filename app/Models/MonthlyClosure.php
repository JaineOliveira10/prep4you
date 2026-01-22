<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyClosure extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'month',
    ];

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'monthly_closure_clients', 'closure_id', 'client_id')
                    ->withPivot('total_simple_labels', 'total_kit_labels', 'total_superkit_labels',
                               'unit_price_simple', 'unit_price_kit',
                               'total_simple_value', 'total_kit_value', 'total_superkit_value',
                               'total_gross', 'total_discount', 'total_net')
                    ->withTimestamps();
    }
}

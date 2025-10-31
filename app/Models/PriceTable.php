<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceTable extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 
        'description', 
        'user_type'
    ];

    protected static function booted()
    {
        static::deleting(function ($priceTable) {
            $priceTable->priceRanges()->delete();
        });
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function priceRanges()
    {
        return $this->hasMany(PriceRange::class);
    }
}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class DistributionCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'acronym', 
        'name'
    ];

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_date',
        'collection_date',
        'status',
        'name',
        'client_id',
        'distribution_center_id',
        'creation_date'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function distributionCenter()
    {
        return $this->belongsTo(DistributionCenter::class);
    }

    public function items()
    {
        return $this->hasMany(ShipmentItem::class);
    }
}

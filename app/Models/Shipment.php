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
        'shipment_code',
        'imported_flag',
        'creation_date',
        'total_value',
        'total_items'
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

    public function pdfs()
    {
        return $this->hasMany(ShipmentPdf::class);
    }
}

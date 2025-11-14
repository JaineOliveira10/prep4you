<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentPdf extends Model
{
    use HasFactory;

    protected $table = 'pdfs_shipments';

    protected $fillable = [
        'shipment_id',
        'type',
        'path_pdf',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
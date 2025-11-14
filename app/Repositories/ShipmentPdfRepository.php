<?php

namespace App\Repositories;

use App\Models\ShipmentPdf;

class ShipmentPdfRepository
{
    public function create(array $data)
    {
        return ShipmentPdf::create($data);
    }

    public function find($id)
    {
        return ShipmentPdf::findOrFail($id);
    }

    public function delete($id)
    {
        $pdf = $this->find($id);
        return $pdf->delete();
    }

    public function countByShipment($shipmentId)
    {
        return ShipmentPdf::where('shipment_id', $shipmentId)->count();
    }
}
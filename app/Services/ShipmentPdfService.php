<?php

namespace App\Services;

use App\Repositories\ShipmentPdfRepository;
use Illuminate\Support\Facades\Storage;

class ShipmentPdfService
{
    protected $shipmentPdfRepository;

    public function __construct(ShipmentPdfRepository $shipmentPdfRepository)
    {
        $this->shipmentPdfRepository = $shipmentPdfRepository;
    }

    public function upload($shipmentId, $file, $tipo)
    {
        if ($this->shipmentPdfRepository->countByShipment($shipmentId) >= 6) {
            throw new \Exception('Limite máximo de 6 PDFs atingido.');
        }

        $path = $file->store("shipments/{$shipmentId}", 'public');

        return $this->shipmentPdfRepository->create([
            'shipment_id' => $shipmentId,
            'type' => $tipo,
            'path_pdf' => $path,
        ]);
    }

    public function delete($id)
    {
        $pdf = $this->shipmentPdfRepository->find($id);
        
        Storage::disk('public')->delete($pdf->path_pdf);
        
        return $this->shipmentPdfRepository->delete($id);
    }
}
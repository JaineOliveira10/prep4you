<?php

namespace App\Services;

use App\Repositories\ShipmentPdfRepository;
use App\Models\ShipmentPdf;
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
            'file_name' => $fileName, // Armazenar o nome do arquivo também
        ]);
    }

    public function delete($id)
    {
        $pdf = $this->shipmentPdfRepository->find($id);
        
        Storage::disk('public')->delete($pdf->path_pdf);
        
        return $this->shipmentPdfRepository->delete($id);
    }

    /**
     * View PDF from shipment
     */
    public function view(ShipmentPdf $pdf)
    {
        try {
            // Verificar se o arquivo existe
            if (!Storage::disk('public')->exists($pdf->path_pdf)) {
                return back()->withErrors(['error' => 'Arquivo não encontrado.']);
            }

            // Retornar o arquivo para visualização (não download)
            $filePath = Storage::disk('public')->path($pdf->path_pdf);
            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($pdf->path_pdf) . '"'
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erro ao visualizar arquivo.']);
        }
    }
}
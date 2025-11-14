<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shipment;
use App\Models\ShipmentPdf;
use App\Services\ShipmentPdfService;
use App\Http\Requests\ShipmentPdfRequest;

class ShipmentPdfController extends Controller
{
    protected $shipmentPdfService;

    public function __construct(ShipmentPdfService $shipmentPdfService)
    {
        $this->shipmentPdfService = $shipmentPdfService;
    }

    /**
     * Upload PDF for shipment
     */
    public function upload(ShipmentPdfRequest $request, Shipment $shipment)
    {

        try {
            $this->shipmentPdfService->upload(
                $shipment->id,
                $request->file('pdf'),
                $request->tipo
            );

            return back()->with('success', 'PDF enviado com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['pdf' => $e->getMessage()]);
        }
    }

    /**
     * Delete PDF from shipment
     */
    public function destroy(ShipmentPdf $pdf)
    {
        try {
            $this->shipmentPdfService->delete($pdf->id);
            return back()->with('success', 'Arquivo excluído com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erro ao excluir PDF.']);
        }
    }
}
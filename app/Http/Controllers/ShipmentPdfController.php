<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shipment;
use App\Models\ShipmentPdf;
use App\Services\ShipmentPdfService;
use App\Http\Requests\ShipmentPdfRequest;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Download all PDFs from shipment as ZIP
     */
    public function downloadPdfs(Shipment $shipment)
    {
        try {
            return $this->shipmentPdfService->downloadPdfsAsZip($shipment);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erro ao fazer download dos PDFs: ' . $e->getMessage()]);
        }
    }
}
<?php

namespace App\Services;

use App\Repositories\ShipmentPdfRepository;
use App\Models\ShipmentPdf;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

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

        $fileName = $file->getClientOriginalName();
        $path = $file->store("shipments/{$shipmentId}", 'public');

        return $this->shipmentPdfRepository->create([
            'shipment_id' => $shipmentId,
            'type' => $tipo,
            'path_pdf' => $path,
            'file_name' => $fileName,
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
    
    /**
     * Download all PDFs from shipment as ZIP
     * Gera ZIP em memória para evitar problemas com sistema de arquivos efêmero no Render
     */
    public function downloadPdfsAsZip($shipment)
    {
        $pdfs = $shipment->pdfs()->get();
        
        if ($pdfs->isEmpty()) {
            throw new \Exception('Nenhum PDF encontrado para esta remessa.');
        }
        
        // Usar stream para criar ZIP em memória
        $tempFile = tempnam(sys_get_temp_dir(), 'zip_');
        
        try {
            $zip = new ZipArchive();
            
            if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Não foi possível criar o arquivo ZIP.');
            }
            
            $typeMap = [
                'master_label' => 'Etiqueta_Master',
                'individual_label' => 'Etiqueta_Individual',
                'invoice' => 'Nota_Fiscal',
            ];
            
            foreach ($pdfs as $pdf) {
                $filePath = Storage::disk('public')->path($pdf->path_pdf);
                
                if (file_exists($filePath)) {
                    $typeLabel = $typeMap[$pdf->type] ?? $pdf->type;
                    $fileName = $shipment->shipment_code . '_' . $typeLabel . '.pdf';
                    $zip->addFile($filePath, $fileName);
                }
            }
            
            $zip->close();
            
            // Ler conteúdo e deletar arquivo temporário
            $zipContent = file_get_contents($tempFile);
            unlink($tempFile);
            
            return response($zipContent)
                ->header('Content-Type', 'application/zip')
                ->header('Content-Disposition', 'attachment; filename="Remessa_' . $shipment->shipment_code . '_PDFs.zip"')
                ->header('Content-Length', strlen($zipContent));
                
        } catch (\Exception $e) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            throw $e;
        }
    }
}
<?php

namespace App\Services;

use App\Models\Shipment;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;

class PreparationOrderService
{
    /**
     * Gerar PDF de Ordem de Preparação em A4
     */
    public function generatePreparationOrderPdf(Shipment $shipment)
    {
        try {
            // Garantir que os diretórios necessários existem
            $this->ensureDirectoriesExist();
            
            // Gerar HTML do PDF
            $html = $this->generateHtml($shipment);
            
            // Configurar opções do dompdf
            $options = new Options();
            $options->set('isPhpEnabled', false);
            $options->set('isRemoteEnabled', true);
            $options->set('tempDir', storage_path('framework/dompdf'));
            $options->set('fontDir', resource_path('fonts'));
            $options->set('fontCache', storage_path('framework/fonts'));
            $options->set('chroot', base_path());
            $options->set('logOutputFile', storage_path('logs/dompdf.log'));
            
            $dompdf = new Dompdf($options);
            
            // Configurar para A4
            $dompdf->setPaper('A4', 'portrait');
            
            $dompdf->loadHtml($html);
            $dompdf->render();
            
            return $dompdf->output();
        } catch (\Exception $e) {
            \Log::error('Erro ao gerar PDF de ordem de preparação: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Erro ao gerar PDF: ' . $e->getMessage());
        }
    }

    /**
     * Garantir que os diretórios necessários existem
     */
    private function ensureDirectoriesExist(): void
    {
        $dirs = [
            storage_path('framework/dompdf'),
            storage_path('framework/fonts'),
            resource_path('fonts'),
        ];
        
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * Gerar HTML da ordem de preparação
     */
    private function generateHtml(Shipment $shipment): string
    {
        // Buscar os itens da remessa com os dados do produto
        $items = $shipment->items()->with('product')->get();

        // Formatar dados da remessa
        $clientName = $shipment->client ? htmlspecialchars($shipment->client->name) : 'N/A';
        $creationDate = $shipment->creation_date ? \Carbon\Carbon::parse($shipment->creation_date)->format('d/m/Y') : 'N/A';
        $shipmentDate = $shipment->shipment_date ? \Carbon\Carbon::parse($shipment->shipment_date)->format('d/m/Y') : 'N/A';
        $collectionDate = $shipment->collection_date ? \Carbon\Carbon::parse($shipment->collection_date)->format('d/m/Y') : 'N/A';
        $collectionName = htmlspecialchars($shipment->name ?? 'N/A');
        $distributionCenter = $shipment->distributionCenter ? htmlspecialchars($shipment->distributionCenter->name) : 'N/A';
        $observations = !empty($shipment->observations) ? htmlspecialchars($shipment->observations) : 'Nenhuma observação';

        // Iniciar HTML
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ordem de Preparação</title>
    <style>
        @page {
            margin: 15mm 15mm 15mm 15mm;
        }

        * {
            margin: 0;
            padding: 10px;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #333;
            background-color: #fff;
        }

        .container {
            width: 100%;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .shipment-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .shipment-info div {
            display: table-row;
        }

        .shipment-info .label {
            display: table-cell;
            font-weight: bold;
            width: 30%;
            padding: 6px;
            border: 1px solid #ddd;
            background-color: #f5f5f5;
        }

        .shipment-info .value {
            display: table-cell;
            padding: 6px;
            border: 1px solid #ddd;
            width: 70%;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            padding: 8px;
            background-color: rgba(234, 106, 18, 0.1);
            border-left: 3px solid rgb(234, 106, 18);
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table thead {
            background-color: rgb(234, 106, 18);
            color: white;
            font-weight: bold;
        }

        .items-table th {
            padding: 8px;
            text-align: left;
            font-size: 10pt;
            border: 1px solid rgb(234, 106, 18);
        }

        .items-table td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 10pt;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .items-table tbody tr:hover {
            background-color: #f0f0f0;
        }

        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border: 1px solid #ddd;
        }

        .product-image-cell {
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .observation {
            font-size: 9pt;
            color: #666;
            max-width: 200px;
            word-wrap: break-word;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            text-align: center;
            font-size: 9pt;
            color: #666;
        }

        .type-badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: bold;
        }

        .type-simple {
            background-color: rgb(234, 106, 18);
            color: #fff;
        }

        .type-kit {
            background-color: rgb(108,117,125);
            color: #fff;
        }

        .type-super-kit {
            background-color: rgb(26,160,83);
            color: #fff;
        }

        .kit-units {
            font-size: 9pt;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- CABEÇALHO -->
        <div class="header">
            ' . (file_exists(base_path('public/images/icons/icon-logo.png')) ? '<img src="' . base_path('public/images/icons/icon-logo.png') . '" alt="Logo" style="max-height: 60px; margin-bottom: 10px;">' : '') . '
            <h1>ORDEM DE PREPARAÇÃO</h1>
            <p>Remessa ' . htmlspecialchars($shipment->shipment_code) . '</p>
        </div>

        <!-- INFORMAÇÕES DA REMESSA -->
        <div class="section-title">Informações da Remessa</div>
        <div class="shipment-info">
            <div>
                <span class="label">Cliente</span>
                <span class="value">' . $clientName . '</span>
            </div>
            <div>
                <span class="label">Data de Criação</span>
                <span class="value">' . $creationDate . '</span>
            </div>
            <div>
                <span class="label">Data da Remessa</span>
                <span class="value">' . $shipmentDate . '</span>
            </div>
            <div>
                <span class="label">Data da Coleta</span>
                <span class="value">' . $collectionDate . '</span>
            </div>
            <div>
                <span class="label">Nome da Coleta</span>
                <span class="value">' . $collectionName . '</span>
            </div>
            <div>
                <span class="label">Centro de Distribuição</span>
                <span class="value">' . $distributionCenter . '</span>
            </div>
        </div>

        <!-- ITENS DA REMESSA -->
        <div class="section-title">Itens para Preparação</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 8%;">Imagem</th>
                    <th style="width: 10%;">FSKU</th>
                    <th style="width: 25%;">Nome do Produto</th>
                    <th style="width: 8%;">Qtd</th>
                    <th style="width: 12%;">Tipo</th>
                    <th style="width: 10%;">Itens Kit</th>
                    <th style="width: 27%;">Observação</th>
                </tr>
            </thead>
            <tbody>';

        // Adicionar itens
        foreach ($items as $item) {
            $product = $item->product;
            
            $fsku = htmlspecialchars($item->fsnku ?? $product->fsnku ?? '-');
            $productName = htmlspecialchars($item->name ?? $product->name ?? '-');
            $quantity = $item->quantity ?? 0;
            $type = strtolower($item->type ?? 'simples');
            $kitUnits = $item->kit_units ?? 0;
            $observation = htmlspecialchars($product->observation ?? '-');

            // Mapear tipo para exibição
            $typeDisplay = match($type) {
                'simples', 'simple' => 'Simples',
                'kit' => 'Kit',
                'super kit', 'super-kit', 'super_kit' => 'Super Kit',
                default => ucfirst($type)
            };

            // Determinar classe CSS para tipo
            $typeClass = match($type) {
                'simples', 'simple' => 'type-simple',
                'kit' => 'type-kit',
                'super_kit', 'super-kit', 'super kit' => 'type-super-kit',
                default => 'type-simple'
            };

            // Construir imagem
            $imageHtml = '-';
            if ($product && $product->photo_path) {
                $base64Image = $this->getImageAsBase64($product->photo_path);
                if ($base64Image !== '-') {
                    $imageHtml = '<img src="' . $base64Image . '" alt="Imagem do produto" class="product-image">';
                }
            }

            $html .= '
                <tr>
                    <td class="product-image-cell">' . $imageHtml . '</td>
                    <td><strong>' . $fsku . '</strong></td>
                    <td>' . $productName . '</td>
                    <td class="text-center"><strong>' . $quantity . '</strong></td>
                    <td><span class="type-badge ' . $typeClass . '">' . $typeDisplay . '</span></td>
                    <td class="text-center">' . ($kitUnits > 0 ? $kitUnits : '-') . '</td>
                    <td><span class="observation">' . $observation . '</span></td>
                </tr>';
        }

        $html .= '
            </tbody>
        </table>

        <div class="section-title">Observações</div>
        <div style="padding: 10px; background-color: #f9f9f9; border-radius:">
            <p>' . $observations . '</p>
        </div>

        <!-- RODAPÉ -->
        <div class="footer">
            <p>Documento gerado em ' . now()->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s') . '</p>
        </div>
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Converter imagem para base64 para embedar no PDF
     */
    private function getImageAsBase64(string $photoPath): string
    {
        try {
            if (!Storage::disk('public')->exists($photoPath)) {
                return '-';
            }

            $fullPath = Storage::disk('public')->path($photoPath);
            
            if (!file_exists($fullPath)) {
                return '-';
            }

            $imageData = file_get_contents($fullPath);
            $mimeType = mime_content_type($fullPath);
            $base64 = base64_encode($imageData);

            return 'data:' . $mimeType . ';base64,' . $base64;
        } catch (\Exception $e) {
            \Log::warning('Erro ao converter imagem para base64: ' . $e->getMessage());
            return '-';
        }
    }
}
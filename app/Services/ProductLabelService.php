<?php

namespace App\Services;

use App\Models\Product;
use Dompdf\Dompdf;
use Dompdf\Options;
use Milon\Barcode\DNS1D;
use Milon\Barcode\Barcode;

class ProductLabelService
{
    /**
     * Gerar PDF com etiquetas de produtos usando Dompdf
     */
    public function generateLabelsPdf(Product $product, int $quantity, float $width, float $height)
    {
        try {
            // Criar diretórios necessários se não existirem
            $this->ensureDirectoriesExist();
            
            $html = $this->generateHtml($product, $quantity, $width, $height);
            
            // Configurar opções do dompdf
            $options = new Options();
            $options->set('isPhpEnabled', false);
            $options->set('isRemoteEnabled', false);
            $options->set('tempDir', storage_path('framework/dompdf'));
            $options->set('fontDir', resource_path('fonts'));
            $options->set('fontCache', storage_path('framework/fonts'));
            $options->set('chroot', base_path());
            $options->set('logOutputFile', storage_path('logs/dompdf.log'));
            
            $dompdf = new Dompdf($options);
            
            // Configurar tamanho do papel personalizado (em pontos: mm * 2.834645669)
            $widthPt = $width * 2.834645669;
            $heightPt = $height * 2.834645669;
            $dompdf->setPaper([0, 0, $widthPt, $heightPt], 'portrait');
            
            $dompdf->loadHtml($html);
            $dompdf->render();
            
            return $dompdf->output();
        } catch (\Exception $e) {
            \Log::error('Erro ao gerar PDF: ' . $e->getMessage(), [
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
     * Gerar barcode usando DNS1D e retornar como HTML img tag com dados base64
     */
    private function generateBarcodeHtml(string $code): string
    {
        try {
            $dns1d = new DNS1D();
            
            // Gerar HTML do barcode (retorna <img> com src em data URI)
            $pngData = (new DNS1D)->getBarcodePNG($code, 'C128', 2, 50);
            $barcodeHtml = '<img src="data:image/png;base64,' . $pngData . '" style="width:100%; height:auto;">';

            
            
            return $barcodeHtml;
        } catch (\Exception $e) {
            \Log::warning('Erro ao gerar barcode: ' . $e->getMessage());
            return '<div style="font-family: monospace; font-size: 12pt; font-weight: bold;">' . htmlspecialchars($code) . '</div>';
        }
    }

    /**
     * Gerar HTML das etiquetas com barcode
     */
    private function generateHtml(Product $product, int $quantity, float $width, float $height): string
    {
        $widthMm = (float)$width;
        $heightMm = (float)$height;

        $fsnku = $product->fsnku ?? '-';
        $productName = $product->name ? htmlspecialchars(substr($product->name, 0, 70)) : '-';
        $fsnkuDisplay = htmlspecialchars($fsnku);

        // Gerar barcode
        $barcodeHtml = $this->generateBarcodeHtml($fsnku);

        // Tamanhos
        $minDimension = min($widthMm, $heightMm);
        $barcodeHeight = max(8, min(25, $heightMm * 0.40));
        $fsnkuFontSize = max(7, min(14, $minDimension * 0.25));
        $productFontSize = max(5, min(9, $minDimension * 0.14));

        $html = '<!DOCTYPE html>
    <html>
    <head>
    <meta charset="UTF-8">
    <title>Etiquetas</title>

    <style>
        @page { margin: 0; padding: 0; }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .label {
            width: '.$widthMm.'mm;
            height: '.$heightMm.'mm;
            overflow: hidden;
            position: relative;

            /* NÃO cria página vazia */
            page-break-inside: avoid;
        }

        .label.page-break {
            page-break-after: always;
        }

        .wrapper {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }

        .barcode-section {
            width: 100%;
            text-align: center;
        }

        .barcode-section img {
            width: 95%;
            height: auto;
            max-height: '.$barcodeHeight.'mm;
            margin-top: 1mm;
        }

        .info {
            text-align: center;
            width: 100%;
            padding: 0 1mm;
        }

        .fsnku-code {
            font-size: '.$fsnkuFontSize.'pt;
            font-weight: bold;
            font-family: "Courier New", monospace;
            margin-top: 0.5mm;
        }

        .product-name {
            font-size: '.$productFontSize.'pt;
            line-height: 1.1;
            margin-top: 0.5mm;
            max-height: 2.5em;
            overflow: hidden;
        }
    </style>

    </head>
    <body>';

        for ($i = 0; $i < $quantity; $i++) {

            $breakClass = ($i < $quantity - 1) ? 'page-break' : '';

            $html .= '
    <div class="label '.$breakClass.'">
        <div class="wrapper">
            <div class="barcode-section">'.$barcodeHtml.'</div>
            <div class="info">
                <div class="fsnku-code">'.$fsnkuDisplay.'</div>
                <div class="product-name">'.$productName.'</div>
            </div>
        </div>
    </div>';
        }

        $html .= '</body></html>';

        return $html;
    }

}
<?php

namespace App\Services;

use Piggly\Pix\StaticPayload;

class PixQrCodeService
{
    private const PIX_KEY = '62.001.080/0001-57';
    private const PIX_NAME = 'Prep4You';
    private const PIX_CITY = 'Sao Paulo';

    /**
     * Gerar QR Code PIX com valor específico
     * Retorna a imagem em base64 com prefixo data:image
     */
    public function generatePixQrCode(float $amount): string
    {
        try {
            // Criar um PIX estático usando a biblioteca piggly/php-pix
            $pix = new StaticPayload();
            
            // Configurar dados do PIX
            // 'document' é usado para CPF ou CNPJ
            $pix->setPixKey('document', self::PIX_KEY)
                ->setMerchantName(self::PIX_NAME)
                ->setMerchantCity(self::PIX_CITY)
                ->setAmount($amount);

            // Obter o Brcode (EMV) - string com os dados PIX formatados
            $brcode = $pix->getPixCode();
            
            \Log::info('PIX Brcode gerado com piggly', [
                'brcode' => $brcode,
                'amount' => $amount,
                'key' => self::PIX_KEY,
                'brcode_length' => strlen($brcode),
            ]);

            // Gerar QR Code em PNG (não SVG) usando chillerlan
            // getQRCode() aplica o QR rendering automaticamente
            $qrImageBase64 = $pix->getQRCode(
                \Piggly\Pix\Enums\QrCode::OUTPUT_PNG,
                \Piggly\Pix\Enums\QrCode::ECC_M
            );
            
            // A biblioteca já retorna em formato data:image/png;base64
            $dataUrl = is_string($qrImageBase64) && strpos($qrImageBase64, 'data:image') === 0 
                ? $qrImageBase64 
                : 'data:image/png;base64,' . $qrImageBase64;
            
            \Log::info('QR Code gerado com sucesso', [
                'brcode' => $brcode,
                'amount' => $amount,
                'base64_length' => strlen($qrImageBase64)
            ]);
            
            return $dataUrl;
        } catch (\Exception $e) {
            \Log::error('Erro ao gerar QR Code PIX: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return '';
        }
    }
}

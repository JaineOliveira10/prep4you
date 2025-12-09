<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductLabelService;
use Illuminate\Http\Request;

class ProductLabelController extends Controller
{
    protected $productLabelService;

    public function __construct(ProductLabelService $productLabelService)
    {
        $this->productLabelService = $productLabelService;
    }

    /**
     * Gerar PDF de etiquetas
     */
    public function generatePdf(Request $request, Product $product)
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|integer|min:1|max:1000',
                'width' => 'required|numeric|min:10|max:200',
                'height' => 'required|numeric|min:10|max:200',
            ], [
                'quantity.required' => 'A quantidade é obrigatória',
                'quantity.integer' => 'A quantidade deve ser um número inteiro',
                'quantity.min' => 'A quantidade mínima é 1',
                'quantity.max' => 'A quantidade máxima é 1000',
                'width.required' => 'A largura é obrigatória',
                'width.numeric' => 'A largura deve ser um número',
                'width.min' => 'A largura mínima é 10mm',
                'width.max' => 'A largura máxima é 200mm',
                'height.required' => 'A altura é obrigatória',
                'height.numeric' => 'A altura deve ser um número',
                'height.min' => 'A altura mínima é 10mm',
                'height.max' => 'A altura máxima é 200mm',
            ]);

            // Gerar PDF
            $pdfContent = $this->productLabelService->generateLabelsPdf(
                $product,
                (int)$validated['quantity'],
                (float)$validated['width'],
                (float)$validated['height']
            );

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="etiquetas_' . $product->fsnku . '.pdf"');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar PDF: ' . $e->getMessage()
            ], 500);
        }
    }
}
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route()->getActionMethod() === 'update';
        
        return [
            'name' => 'nullable|string|max:255',
            'shipment_date' => 'required|date',
            'collection_date' => 'nullable|date|after_or_equal:shipment_date',
            'status' => 'required|in:Pending,In Preparation,Packed,Collected,Invoice Generated,Paid',
            'client_id' => 'required|exists:clients,id',
            'distribution_center_id' => 'required|exists:distribution_centers,id',
            'shipment_code' => 'nullable|string|max:20',
            'imported_flag' => 'nullable|boolean',
            'creation_date' => 'required|date',
            'total_value' => 'nullable|numeric|min:0',
            'total_items' => 'nullable|integer|min:0',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
            'pdfs' => 'nullable|array|max:6',
            'pdfs.*.tipo' => $isUpdate ? 'nullable|in:individual_label,master_label,invoice' : 'required_with:pdfs.*.pdf|in:individual_label,master_label,invoice',
            'pdfs.*.pdf' => 'required_with:pdfs.*.tipo|mimes:pdf|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da remessa é obrigatório.',
            'shipment_date.required' => 'A data da remessa é obrigatória.',
            'client_id.required' => 'O cliente é obrigatório.',
            'distribution_center_id.required' => 'O centro de distribuição é obrigatório.',
            'items.required' => 'Pelo menos um item é obrigatório.',
            'items.min' => 'A remessa deve ter pelo menos um item.',
            'items.*.product_id.required' => 'O produto é obrigatório.',
            'items.*.quantity.required' => 'A quantidade é obrigatória.',
            'items.*.quantity.min' => 'A quantidade deve ser pelo menos 1.',
            'items.*.unit_price.required' => 'O preço unitário é obrigatório.',
            'pdfs.max' => 'Você pode enviar no máximo 6 PDFs.',
            'pdfs.*.tipo.required_with' => 'O tipo do PDF é obrigatório quando um arquivo é selecionado.',
            'pdfs.*.tipo.in' => 'O tipo do PDF deve ser: Etiqueta Individual, Etiqueta Master ou Nota Fiscal.',
            'pdfs.*.pdf.required_with' => 'O arquivo PDF é obrigatório quando um tipo é selecionado.',
            'pdfs.*.pdf.mimes' => 'O arquivo deve ser um PDF válido.',
            'pdfs.*.pdf.max' => 'O arquivo PDF deve ter no máximo 5MB.',
        ];
    }
}
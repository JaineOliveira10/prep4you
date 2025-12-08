<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route()->getActionMethod() === 'update';
        
        $shipmentId = null;
        
        if ($isUpdate) {
            $shipmentId = $this->route('shipment');
            
            // Se for string, buscar no banco
            if (is_string($shipmentId)) {
                $shipmentId = $shipmentId;
            }
        }
        
        $uniqueRule = $isUpdate && $shipmentId
            ? Rule::unique('shipments', 'shipment_code')->ignore($shipmentId)
            : 'unique:shipments,shipment_code';

        return [
            'name' => 'nullable|string|max:255',
            'shipment_date' => 'required|date',
            'collection_date' => 'nullable|date|after_or_equal:shipment_date',
            'status' => 'required|in:Pending,In Preparation,Packed,Collected,Invoice Generated,Paid',
            'client_id' => 'required|exists:clients,id',
            'distribution_center_id' => 'required|exists:distribution_centers,id',
            'shipment_code' => ['nullable', 'string', 'max:20', $uniqueRule],
            'imported_flag' => 'nullable|boolean',
            'creation_date' => 'required|date',
            'total_value' => 'nullable|numeric|min:0',
            'total_items' => 'nullable|integer|min:0',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_if:items,!=null|exists:products,id',
            'items.*.quantity' => 'required_if:items,!=null|integer|min:1',
            'items.*.unit_price' => 'required_if:items,!=null|numeric|min:0',
            'pdfs' => 'required|array|size:3',
            'pdfs.*.tipo' => 'required|in:individual_label,master_label,invoice|distinct:strict',
            'pdfs.*.pdf' => 'required|mimes:pdf|max:5120',
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
            'items.*.product_id.required_if' => 'O produto é obrigatório.',
            'items.*.quantity.required_if' => 'A quantidade é obrigatória.',
            'items.*.quantity.min' => 'A quantidade deve ser pelo menos 1.',
            'items.*.unit_price.required_if' => 'O preço unitário é obrigatório.',
            'pdfs.required' => 'Você precisa fazer upload de exatamente 3 PDFs (um de cada tipo).',
            'pdfs.size' => 'Você deve fazer upload de exatamente 3 PDFs, um de cada tipo.',
            'pdfs.*.tipo.required' => 'O tipo do PDF é obrigatório.',
            'pdfs.*.tipo.in' => 'Os tipos devem ser: Etiqueta individual, Etiqueta master ou Nota fiscal.',
            'pdfs.*.tipo.distinct' => 'Você deve fornecer um PDF de cada tipo.',
            'pdfs.*.pdf.required' => 'Todos os PDFs são obrigatórios.',
            'pdfs.*.pdf.mimes' => 'O arquivo deve ser um PDF válido.',
            'pdfs.*.pdf.max' => 'O arquivo PDF deve ter no máximo 5MB.',
        ];
    }
}
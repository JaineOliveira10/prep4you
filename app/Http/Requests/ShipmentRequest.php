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
        $shipment = null;
        
        if ($isUpdate) {
            $shipmentId = $this->route('shipment');
            
            // Se for string, buscar no banco
            if (is_string($shipmentId)) {
                $shipment = \App\Models\Shipment::find($shipmentId);
            } else {
                $shipment = $shipmentId;
            }
        }
        
        $uniqueRule = $isUpdate && $shipmentId
            ? Rule::unique('shipments', 'shipment_code')->ignore($shipmentId)
            : 'unique:shipments,shipment_code';

        // Verificar se é importada E não tem PDFs
        $isImported = $shipment ? $shipment->imported_flag : false;
        $hasPdfs = $shipment ? $shipment->pdfs()->count() > 0 : false;
        
        // PDFs obrigatórios apenas se for importada E não tiver PDFs ainda
        $requirePdfs = ($isImported && !$hasPdfs);
        
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
            'pdfs' => $requirePdfs ? 'required|array|min:1' : 'nullable|array',
            'pdfs.*.tipo' => $requirePdfs ? 'required|in:individual_label,master_label,invoice' : 'nullable|in:individual_label,master_label,invoice',
            'pdfs.*.pdf' => $requirePdfs ? 'required|mimes:pdf|max:5120' : 'nullable|mimes:pdf|max:5120',
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
            'pdfs.required' => 'Você precisa fazer upload de pelo menos um PDF para remessas importadas antes de salvar.',
            'pdfs.*.tipo.required' => 'O tipo do PDF é obrigatório.',
            'pdfs.*.pdf.required' => 'Você precisa fazer upload de pelo menos um PDF.',
            'pdfs.*.pdf.mimes' => 'O arquivo deve ser um PDF válido.',
            'pdfs.*.pdf.max' => 'O arquivo PDF deve ter no máximo 5MB.',
        ];
    }
}
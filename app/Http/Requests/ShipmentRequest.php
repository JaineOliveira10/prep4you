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
        return [
            'name' => 'nullable|string|max:255',
            'shipment_date' => 'required|date',
            'collection_date' => 'nullable|date|after_or_equal:shipment_date',
            'status' => 'required|in:Pending,In Preparation,Packed,Collected,Invoice Generated,Paid',
            'client_id' => 'required|exists:clients,id',
            'distribution_center_id' => 'required|exists:distribution_centers,id',
            'creation_date' => 'required|date',
            'total_value' => 'nullable|numeric|min:0',
            'total_items' => 'nullable|integer|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
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
        ];
    }
}
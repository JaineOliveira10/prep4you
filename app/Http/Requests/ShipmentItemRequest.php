<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShipmentItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipment_id' => 'required|exists:shipments,id',
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:50',
            'fsnku' => 'nullable|string|max:15',
            'sku' => 'nullable|string|max:40',
            'type' => 'required|in:simple,kit,super_kit',
            'kit_units' => 'nullable|integer|min:1',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'total_value' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'shipment_id.required' => 'A remessa é obrigatória.',
            'product_id.required' => 'O produto é obrigatório.',
            'name.required' => 'O nome do produto é obrigatório.',
            'type.required' => 'O tipo do produto é obrigatório.',
            'quantity.required' => 'A quantidade é obrigatória.',
            'quantity.min' => 'A quantidade deve ser pelo menos 1.',
            'unit_price.required' => 'O preço unitário é obrigatório.',
            'total_value.required' => 'O valor total é obrigatório.',
        ];
    }
}
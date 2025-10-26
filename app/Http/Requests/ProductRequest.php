<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:50',
            'asin' => 'nullable|string|max:15',
            'fsnku' => 'nullable|string|max:15',
            'sku' => 'nullable|string|max:40',
            'photo' => 'nullable|image|max:2048',
            'observation' => 'nullable|string|max:200',
            'type' => 'required|in:simple|kit|super_kit',
            'kit_units' => 'nullable|integer|min:1|required_if:type|kit|super_kit',
            'unit_price' => 'nullable|numeric|min:0|required_if:type,super_kit',
            'client_id' => 'required|exists:users,id',

        ];
    }
}
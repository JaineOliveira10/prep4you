<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PriceTableRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'ranges' => 'required|array|min:1',
            'ranges.*.min_value' => 'required|integer|min:0',
            'ranges.*.max_value' => 'required|integer|min:0',
            'ranges.*.price' => 'required|string',
            'ranges.*.price_kit' => 'required|string',
        ];
    }
}
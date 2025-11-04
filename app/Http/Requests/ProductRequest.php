<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        
        $productId = $this->route('product');

        $rules = [
            'name' => 'required|string|max:50',
            'asin' => $productId ? "nullable|string|max:15|unique:products,asin,{$productId},id,deleted_at,NULL" : 'nullable|string|max:15|unique:products,asin,NULL,id,deleted_at,NULL',
            'fsnku' => $productId ? "nullable|string|max:15|unique:products,fsnku,{$productId},id,deleted_at,NULL" : 'nullable|string|max:15|unique:products,fsnku,NULL,id,deleted_at,NULL',
            'sku' => 'required|string|max:40',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'observation' => 'nullable|string|max:200',
            'type' => 'required|in:simple,kit,super_kit',
            'client_id' => 'required|exists:clients,id',
        ];

        if ($this->type == 'kit' || $this->type == 'super_kit') {
            $rules['kit_units'] = 'required|integer|min:1';
        }

        if ($this->type == 'super_kit') {
            $rules['unit_price'] = 'required|numeric|min:0';
        }

        return $rules;
    }

    public function prepareForValidation()
    {
        // Se for cliente, força o client_id do usuário logado
        if (auth()->user()->type == 'client') {
            $this->merge(['client_id' => auth()->user()->client_id]);
        }
        // Se não tem client_id e não é admin, usa o client_id do usuário
        elseif (!$this->has('client_id') || !$this->client_id) {
            $this->merge(['client_id' => auth()->user()->client_id ?? null]);
        }

        if ($this->unit_price) {
            $price = str_replace(['.', ','], ['', '.'], $this->unit_price);
            $this->merge(['unit_price' => $price]);
        }
    }

    public function messages()
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.max' => 'O nome deve ter no máximo 50 caracteres.',
            'asin.unique' => 'Este ASIN já está em uso.',
            'fsnku.unique' => 'Este FSNKU já está em uso.',
            'sku.unique' => 'Este SKU já está em uso.',
            'type.required' => 'O tipo é obrigatório.',
            'kit_units.required' => 'O número de unidades é obrigatório para kits.',
            'unit_price.required' => 'O preço unitário é obrigatório para super kits.',
            'client_id.required' => 'O cliente é obrigatório.',
        ];
    }
}
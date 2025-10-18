<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        return true; // ou política de acesso
    }

    public function rules()
    {
        $id = $this->route('user');

        return [
            'first_name' => 'required|string|max:255',
            'email' => $id ? "required|email|unique:users,email,{$id}" : 'required|email|unique:users,email',
            'password' => $this->isMethod('post')
                ? 'required|min:6'
                : 'nullable|min:6',
            'type' => 'required|in:admin,client',
            'city' => 'nullable|string|max:255',
            'uf' => 'nullable|string|max:2',
            'phone' => 'nullable|string|max:20',
            'price_table_id' => 'nullable|exists:price_tables,id'
        ];
    }
}

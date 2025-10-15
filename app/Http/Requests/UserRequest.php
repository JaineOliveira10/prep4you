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
        $id = $this->route('id');

        return [
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$id}",
            'password' => $this->isMethod('post')
                ? 'required|min:6'
                : 'nullable|min:6',
            'type' => 'required|in:admin,user'
        ];
    }
}

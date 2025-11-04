<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => __('passwords.required_current'),
            'password.required' => __('passwords.required_new'),
            'password.min' => __('passwords.min_length', ['min' => 6]),
            'password.confirmed' => __('passwords.confirmation_mismatch'),
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => __('validation.attributes.current_password'),
            'password' => __('validation.attributes.password'),
            'password_confirmation' => __('validation.attributes.password_confirmation'),
        ];
    }
}
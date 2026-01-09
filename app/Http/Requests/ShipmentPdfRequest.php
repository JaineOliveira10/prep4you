<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShipmentPdfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pdf' => 'required|mimes:pdf|max:10240',
            'tipo' => 'required|in:individual_label,master_label,invoice',
        ];
    }

    public function messages(): array
    {
        return [
            'pdf.required' => 'O arquivo PDF é obrigatório.',
            'pdf.mimes' => 'O arquivo deve ser um PDF.',
            'pdf.max' => 'O arquivo não pode ser maior que 5MB.',
            'tipo.required' => 'O tipo do PDF é obrigatório.',
            'tipo.in' => 'Tipo de PDF inválido.',
        ];
    }
}
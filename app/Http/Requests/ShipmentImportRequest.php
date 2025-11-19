<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShipmentImportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'tsv_file' => 'required|file|mimes:tsv,txt'
        ];
    }

    public function messages()
    {
        return [
            'tsv_file.required' => 'O arquivo TSV é obrigatório.',
            'tsv_file.file' => 'Deve ser um arquivo válido.',
            'tsv_file.mimes' => 'O arquivo deve ser do tipo TSV ou TXT.'
        ];
    }
}
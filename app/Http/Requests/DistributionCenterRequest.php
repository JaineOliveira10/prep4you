<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class DistributionCenterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'acronym' => 'required|string|max:7',
            'name' => 'required|string|max:255|unique:distribution_centers,name',
        ];
    }
}
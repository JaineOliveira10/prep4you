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
        $id = $this->route('distribution_center');
        
        return [
            'acronym' => $id ? "required|string|max:7|unique:distribution_centers,acronym,{$id},id,deleted_at,NULL" : 'required|string|max:7|unique:distribution_centers,acronym,NULL,id,deleted_at,NULL',
            'name' => 'required|string|max:255',
        ];
    }
}
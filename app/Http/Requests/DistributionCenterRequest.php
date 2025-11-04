<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'acronym' =>  $id ? 'required|string|max:7|unique:distribution_centers,acronym,' . $id . ',id' : 'required|string|max:7|unique:distribution_centers,acronym',
            'name' => 'required|string|max:255',
        ];
    }
}

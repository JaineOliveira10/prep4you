<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PriceTableRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('price_table');
        
        return [
            'name' => $id ? 'required|string|max:255|unique:price_tables,name,' . $id . ',id' : 'required|string|max:255|unique:price_tables,name',
            'description' => 'nullable|string|max:255',
            'ranges' => 'required|array|min:1',
            'ranges.*.min_value' => 'required|integer|min:0',
            'ranges.*.max_value' => 'required|integer|min:0',
            'ranges.*.price' => 'required|string',
            'ranges.*.price_kit' => 'required|string',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $ranges = $this->input('ranges', []);
            
            foreach ($ranges as $i => $range) {
                if (!isset($range['min_value']) || !isset($range['max_value'])) {
                    continue;
                }
                
                $min = (int) $range['min_value'];
                $max = (int) $range['max_value'];
                
                if ($min > $max) {
                    $validator->errors()->add("ranges.{$i}.max_value", __('validation.custom.ranges.max_greater_than_min'));
                    continue;
                }
                
                foreach ($ranges as $j => $otherRange) {
                    if ($i === $j || !isset($otherRange['min_value']) || !isset($otherRange['max_value'])) {
                        continue;
                    }
                    
                    $otherMin = (int) $otherRange['min_value'];
                    $otherMax = (int) $otherRange['max_value'];
                    
                    if ($this->rangesOverlap($min, $max, $otherMin, $otherMax)) {
                        $validator->errors()->add("ranges.{$i}", __('validation.custom.ranges.overlap', ['range1' => "{$min}-{$max}", 'range2' => "{$otherMin}-{$otherMax}"]));
                        break;
                    }
                }
            }
        });
    }

    private function rangesOverlap($min1, $max1, $min2, $max2)
    {
        return $min1 <= $max2 && $max1 >= $min2;
    }
}
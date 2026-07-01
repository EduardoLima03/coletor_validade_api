<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => 'required|integer|unique:products,code,' . $this->route('product'),
            'description' => 'required|string|max:255',
            'custo' => 'nullable|regex:/^[\d.,]*$/',
        ];
    }
}

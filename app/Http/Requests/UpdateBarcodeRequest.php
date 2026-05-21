<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBarcodeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'ean' => 'required|integer|unique:barcodes,ean,' . $this->route('ean'),
            'product_id' => 'required|integer|exists:products,id',
        ];
    }
}

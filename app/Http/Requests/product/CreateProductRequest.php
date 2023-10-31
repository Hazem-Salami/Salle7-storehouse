<?php

namespace App\Http\Requests\product;

use App\Http\Requests\BaseRequest;

class CreateProductRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:25',
            'description' => 'required|string',
            'product_code' => 'required|string|max:25',
            'made' => 'required|string|max:25|unique:products,made,null,null,product_code,' . $this->product_code . ',user_id,' . auth()->user()->id,
            'price' => 'required|numeric',
            'product_photo' => 'required|max:20000|mimes:bmp,jpg,png,jpeg,svg',
        ];
    }

    public function messages()
    {
        return [
            'made.unique' => 'The made, product code and user ID have already been taken.',
        ];
    }
}

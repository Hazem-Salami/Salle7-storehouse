<?php

namespace App\Http\Requests\product;

use App\Http\Requests\BaseRequest;

class UpdateProductRequest extends BaseRequest
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
            'name' => 'string|max:25',
            'description' => 'string',
            'product_code' => 'required_with:made|string|max:25',
            'made' => 'required_with:product_code|string|max:25|unique:products,made,'.$this->id.',id,product_code,' . $this->product_code . ',user_id,' . auth()->user()->id,
            'price' => 'numeric|min:0',
            'quantity' => 'numeric|min:1',
            'category_id' => 'numeric|exists:categories,id',
            'product_photo' => 'max:20000|mimes:bmp,jpg,png,jpeg,svg',
        ];
    }

    public function messages()
    {
        return [
            'made.unique' => 'The made, product code and user ID have already been taken.',
        ];
    }
}

<?php

namespace App\Http\Requests\auth;

use App\Http\Requests\BaseRequest;

class StorehouseAuthFileRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'storehouse_photo' => 'required|array',
            'storehouse_photo.*' => 'max:20000|mimes:bmp,jpg,png,jpeg,svg',
            'IDphoto' => 'required|array',
            'IDphoto.*' => 'max:20000|mimes:bmp,jpg,png,jpeg,svg',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ];
    }
}

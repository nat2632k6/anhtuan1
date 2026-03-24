<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'label' => 'required|max:50|string',
            'phone' => 'required|regex:/^[0-9]{10,11}$/',
            'address' => 'required|max:500|string',
            'is_default' => 'nullable|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'label.required' => 'Tên địa chỉ là bắt buộc',
            'phone.required' => 'Số điện thoại là bắt buộc',
            'phone.regex' => 'Số điện thoại phải có 10-11 chữ số',
            'address.required' => 'Địa chỉ là bắt buộc'
        ];
    }
}

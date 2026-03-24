<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|max:255|string',
            'email' => 'required|email|max:255',
            'phone' => 'required|regex:/^[0-9]{10,11}$/',
            'address' => 'required|max:500|string',
            'payment_method' => 'required|in:cod,bank_transfer',
            'save_address' => 'nullable|boolean',
            'address_label' => 'nullable|max:50|string',
            'set_default' => 'nullable|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên khách hàng là bắt buộc',
            'email.required' => 'Email là bắt buộc',
            'email.email' => 'Email không hợp lệ',
            'phone.required' => 'Số điện thoại là bắt buộc',
            'phone.regex' => 'Số điện thoại phải có 10-11 chữ số',
            'address.required' => 'Địa chỉ là bắt buộc',
            'payment_method.required' => 'Phương thức thanh toán là bắt buộc'
        ];
    }
}

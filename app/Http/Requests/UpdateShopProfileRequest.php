<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShopProfileRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->role === 'shop';
    }

    public function rules()
    {
        $userId = auth()->id();

        return [
            'shop_name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => 'required|string|max:30',
            'shop_address' => 'required|string|max:255',
            'motto' => 'nullable|string|max:255',
            'services_provided' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'image|mimes:jpeg,jpg,png,webp|max:4096',
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'You have already an account with this email',
        ];
    }
}

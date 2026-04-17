<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopProfileRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->role === 'shop';
    }

    public function rules()
    {
        $currentEmail = (string) (auth()->user()->email ?? '');

        return [
            'shop_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                function ($attribute, $value, $fail) use ($currentEmail) {
                    if (strtolower(trim((string) $value)) !== strtolower(trim($currentEmail))) {
                        $fail('You cannot change your email.');
                    }
                },
            ],
            'phone' => 'required|string|max:30',
            'shop_address' => 'required|string|max:255',
            'motto' => 'nullable|string|max:255',
            'services_provided' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'image|mimes:jpeg,jpg,png,webp|max:4096',
        ];
    }
}

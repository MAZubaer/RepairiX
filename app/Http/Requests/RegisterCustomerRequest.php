<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RegisterCustomerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'location' => 'required|string|max:255',
            'primary_device' => 'required|string|max:255',
            'password' => 'required|string|confirmed|min:8',
        ];

        if (Schema::hasColumn('users', 'phone')) {
            $rules['phone'] = 'required|string|max:30';
        } else {
            // If column missing, keep request shape consistent.
            $rules['phone'] = 'required|string|max:30';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'email.unique' => 'You have already an account with this email',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        Log::warning('Customer registration validation failed', [
            'ip' => $this->ip(),
            'input' => $this->except('password', 'password_confirmation'),
            'errors' => $validator->errors()->toArray(),
            'session_id' => session()->getId(),
        ]);

        parent::failedValidation($validator);
    }
}

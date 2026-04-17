<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRecordRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->role === 'customer';
    }

    public function rules()
    {
        return [
            'phone_model' => 'required|string|max:255',
            'phone_number' => 'required|string|max:30',
            'phone_imei_number' => 'required|string|max:50',
            'customer_problem' => 'required|string|max:5000',
        ];
    }
}

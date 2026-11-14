<?php

namespace App\Http\Requests;

use App\Support\DeviceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRecordRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->role === 'customer';
    }

    public function attributes()
    {
        return [
            'device_type' => 'device type',
            'phone_model' => 'device model',
            'phone_number' => 'contact number',
            'phone_imei_number' => 'serial / IMEI number',
            'customer_problem' => 'problem description',
        ];
    }

    public function rules()
    {
        return [
            'device_type' => ['required', Rule::in(DeviceType::ALL)],
            // Column names predate laptops/tablets: phone_model = device model, phone_imei_number = serial or IMEI.
            'phone_model' => 'required|string|max:255',
            'phone_number' => 'required|string|max:30',
            'phone_imei_number' => 'nullable|string|max:50',
            'customer_problem' => 'required|string|max:5000',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CourierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $presence  = $this->isMethod('PATCH') ? 'sometimes' : 'required';
        $courierId = $this->route('courier')?->id; // null saat store

        return [
            'name'          => [$presence, 'string', 'max:255'],
            'phone'         => [$presence, 'string', 'regex:/^(\+62|62|0)8[1-9][0-9]{6,11}$/', Rule::unique('couriers', 'phone')->ignore($courierId)],
            'email'         => ['nullable', 'email', 'max:255', Rule::unique('couriers', 'email')->ignore($courierId)],
            'address'       => ['nullable', 'string', 'max:1000'],
            'vehicle_type'  => [$presence, Rule::in(Courier::VEHICLE_TYPES)],
            'license_plate' => ['nullable', 'string', 'max:15'],
            'level'         => [$presence, 'integer', 'between:1,5'],
            'is_active'     => ['sometimes', 'boolean'],
        ];
    }
}

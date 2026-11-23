<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCourierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('level'))) {
            $this->merge([
                'level' => array_values(array_filter(explode(',', $this->input('level')), fn ($v) => $v !== '')),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search'    => ['nullable', 'string', 'max:100'],
            'level'     => ['nullable', 'array'],
            'level.*'   => ['integer', 'between:1,5'],
            'sort'      => ['nullable', Rule::in(['name', 'created_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page'  => ['nullable', 'integer', 'between:1,100'],
        ];
    }
}

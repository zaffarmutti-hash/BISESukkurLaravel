<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class ChallanGenerationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // TODO: Add validation rules
        ];
    }
}

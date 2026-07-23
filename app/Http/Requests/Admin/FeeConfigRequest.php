<?php

namespace App\Http\Requests\Admin;

use App\Models\FeeStructure;
use App\Services\BoardPolicyService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeeConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        $yearId = app(BoardPolicyService::class)->activeYear()?->id;
        $fee = $this->resolveFeeStructure();

        return [
            'class_level' => [
                'required',
                Rule::in(['ssc_part1', 'ssc_part2', 'hsc_part1', 'hsc_part2']),
                Rule::unique('fee_structures')
                    ->where(fn ($q) => $q
                        ->where('academic_year_id', $yearId)
                        ->where('student_type', $this->input('student_type'))
                        ->where('fee_type', $this->input('fee_type')))
                    ->ignore($fee?->id),
            ],
            'student_type' => [
                'required',
                Rule::in(['fresh', 'repeater', 'private']),
            ],
            'fee_type' => [
                'required',
                Rule::in(['enrollment', 'examination']),
            ],
            'amount_paisas' => ['required', 'integer', 'min:1'],
            'late_fee_surcharge_paisas' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'academic_year_id' => ['prohibited'],
            'school_id' => ['prohibited'],
        ];
    }

    protected function resolveFeeStructure(): ?FeeStructure
    {
        $fee = $this->route('feeStructure') ?? $this->route('fee');

        return $fee instanceof FeeStructure ? $fee : null;
    }
}

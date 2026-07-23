<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class EnrollmentFormRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'full_name'     => ['required', 'string', 'max:200'],
            'father_name'   => ['required', 'string', 'max:200'],
            'gender'        => ['required', 'in:male,female,other'],
            'class_level'   => ['required', 'in:ssc_part1,ssc_part2,hsc_part1,hsc_part2'],
            'subject_group' => ['required', 'in:science,arts,commerce,general,pre_medical,pre_engineering'],
            'student_type'  => ['required', 'in:fresh,repeater,private'],
            'save_as'       => ['required', 'in:final'],

            'gr_number'               => ['required', 'string', 'max:50'],
            'admission_date'          => ['required', 'date'],
            'date_of_birth'           => ['required', 'date', 'before:today'],
            'cnic'                    => ['required_without:b_form', 'nullable', 'string', 'regex:/^\d{5}-\d{7}-\d{1}$/'],
            'b_form'                  => ['required_without:cnic', 'nullable', 'string', 'regex:/^\d{5}-\d{7}-\d{1}$/'],
            'father_cnic'             => ['nullable', 'string', 'regex:/^\d{5}-\d{7}-\d{1}$/'],
            'guardian_name'           => ['nullable', 'string', 'max:200'],
            'guardian_cnic'           => ['nullable', 'string', 'regex:/^\d{5}-\d{7}-\d{1}$/'],
            'surname'                 => ['required', 'string', 'max:100'],
            'marks_of_identification' => ['required', 'string', 'max:200'],
            'religion'                => ['required', 'string', 'max:50'],
            'medium_of_instruction'   => ['nullable', 'string', 'max:50'],
            'phone'                   => ['nullable', 'string', 'regex:/^03[0-9]{2}-[0-9]{7}$/'],
            'email'                   => ['nullable', 'email', 'max:100'],
            'postal_code'             => ['nullable', 'string', 'max:10'],
            'address'                 => ['nullable', 'string', 'max:500'],
            'remarks'                 => ['nullable', 'string', 'max:1000'],
            'photo'                   => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'cnic.regex'              => 'CNIC must match format: XXXXX-XXXXXXX-X.',
            'b_form.regex'            => 'B-Form must match format: XXXXX-XXXXXXX-X.',
            'father_cnic.regex'       => 'Father CNIC must match format: XXXXX-XXXXXXX-X.',
            'guardian_cnic.regex'     => 'Guardian CNIC must match format: XXXXX-XXXXXXX-X.',
            'phone.regex'             => 'Phone must be a valid Pakistani mobile number (e.g. 0300-1234567).',
            'cnic.required_without'   => 'Either CNIC or B-Form number is required for final submission.',
            'b_form.required_without' => 'Either CNIC or B-Form number is required for final submission.',
        ];
    }
}

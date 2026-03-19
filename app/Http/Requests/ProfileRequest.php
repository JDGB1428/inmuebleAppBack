<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
        $mergeData = [];

        // Decodificamos specialties si es un string
        if ($this->has('specialties') && is_string($this->specialties)) {
            $mergeData['specialties'] = json_decode($this->specialties, true);
        }

        // Decodificamos social_links si es un string
        if ($this->has('social_links') && is_string($this->social_links)) {
            $mergeData['social_links'] = json_decode($this->social_links, true);
        }

        // Si encontramos alguno, lo fusionamos con los datos del request
        if (!empty($mergeData)) {
            $this->merge($mergeData);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'address'             => 'nullable|string|max:255',
            'phone'               => 'nullable|string|max:20',
            'whatsapp'            => 'nullable|string|max:20',
            'years_of_experience' => 'nullable|integer|min:0|max:100',
            'job_title'           => 'nullable|string|max:255',
            'specialties'         => 'nullable|array',
            'social_links'        => 'nullable|array',
            'avatar'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'license_number'      => 'nullable|string|max:20',
        ];
    }
}

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
            'nationality'         => 'nullable|string'
        ];
    }

    public function messages(): array
    {
        return [
            'address.string'              => 'La dirección debe ser un texto válido.',
            'address.max'                 => 'La dirección no puede exceder los 255 caracteres.',
            'phone.string'                => 'El número de teléfono debe ser un texto válido.',
            'phone.max'                   => 'El número de teléfono no puede exceder los 20 caracteres.',
            'whatsapp.string'             => 'El número de WhatsApp debe ser un texto válido.',
            'whatsapp.max'                => 'El número de WhatsApp no puede exceder los 20 caracteres.',
            'years_of_experience.integer' => 'Los años de experiencia deben ser un número entero.',
            'years_of_experience.min'     => 'Los años de experiencia no pueden ser un valor negativo.',
            'years_of_experience.max'     => 'Los años de experiencia no pueden ser mayores a 100.',
            'job_title.string'            => 'El cargo o título profesional debe ser un texto válido.',
            'job_title.max'               => 'El cargo no puede exceder los 255 caracteres.',
            'specialties.array'           => 'Las especialidades deben enviarse en un formato de lista válido.',
            'social_links.array'          => 'Los enlaces sociales deben enviarse en un formato de lista válido.',
            'avatar.image'                => 'El archivo subido para el avatar debe ser una imagen.',
            'avatar.mimes'                => 'El avatar debe estar en uno de los siguientes formatos: jpeg, png, jpg o webp.',
            'avatar.max'                  => 'El peso de la imagen del avatar no debe superar los 2MB.',
            'license_number.string'       => 'El número de licencia debe ser un texto válido.',
            'license_number.max'          => 'El número de licencia no puede exceder los 20 caracteres.',
            'nationality.string'          => 'La nacionalidad debe ser un texto válido.',
        ];
    }
}

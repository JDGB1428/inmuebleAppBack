<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'number'],
            'description' => ['required', 'max:500'],
            'price' => ['required', 'number'],
            'direction' => ['required', ],
            'room' => ['required', 'number'],
            'area_m2' => ['required', 'number'],
            'bathrooms' => ['required', 'number'],
            'state' => ['required'],
            'images' => ['image','mimes:jpeg,png,jpg,gif', 'max:2048']
        ];
    }

    public function messages(): array {
        return [
            'title.required' => 'El titulo es obligatorio',
            'description.required' => 'El campo descripcion es obligatorio',
            'price.required' => 'El campo precio es obligatorio',
            'direction.required' => 'El campo direccion es obligatorio',
            'room.required' => 'El campo es obligatorio',
            'area_m2.required' => 'El campo es obligatorio',
            'bathrooms.required' => 'El campo es obligatorio',
            'state.required' => 'El estado es obligatorio',
            'images' => 'La imagen es obligatoria',

            'description.max' => 'El campo descripcion es hasta maximo 500 caracteres',
            'price.number' => 'El campo precio solo acepta numeros',
            'room.number' => 'El campo room solo acepta numeros',
            'area_m2.number' => 'El campo solo acepta numeros',
            'bathrooms.number' => 'El campo solo acepta numeros',

        ];
    }
}

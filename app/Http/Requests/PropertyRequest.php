<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'title' => ['required'],
            'description' => ['required', 'max:500'],
            'price' => ['required', 'numeric', 'min:0'],
            'direction' => ['required',],
            'room' => ['required', 'numeric'],
            'area_m2' => ['required', 'numeric'],
            'bathrooms' => ['required', 'numeric'],
            'state' => [
                'required',
                Rule::in(['available', 'not-available', 'published', 'rented', 'sold'])
            ],
            'category_id' =>['required','exists:categories,id'],
            'image' => 'required|array|min:1', // Debe ser un array
            'image.*' => 'image|mimes:jpeg,png,jpg|max:10240'
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El titulo es obligatorio',
            'description.required' => 'El campo descripcion es obligatorio',
            'price.required' => 'El campo precio es obligatorio',
            'direction.required' => 'El campo direccion es obligatorio',
            'room.required' => 'El campo es obligatorio',
            'area_m2.required' => 'El campo es obligatorio',
            'bathrooms.required' => 'El campo es obligatorio',
            'state.required' => 'El estado es obligatorio',
            'image.required' => 'La imagen es obligatoria',
            'category_id' => 'La categoria es requerida',

            'description.max' => 'El campo descripcion es hasta maximo 500 caracteres',
            'price.numeric' => 'El campo precio solo acepta numeros',
            'room.numeric' => 'El campo room solo acepta numeros',
            'area_m2.numeric' => 'El campo solo acepta numeros',
            'bathrooms.numeric' => 'El campo solo acepta numeros',

        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Categories;
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

    protected function prepareForValidation()
    {
        $features = $this->input('features');

        if (is_array($features)) {
            $formattedFeatures = [];

            foreach ($features as $key => $value) {
                // 1. SOLUCIÓN: Si es 'administration', lo conservamos como número.
                if ($key === 'administration') {
                    $formattedFeatures[$key] = is_numeric($value) ? (float) $value : 0;
                }
                // 2. Si es cualquier otra cosa (piscina, bbq), lo convertimos a booleano
                else {
                    $formattedFeatures[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                }
            }

            $this->merge([
                'features' => $formattedFeatures
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
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
            'category_id' => ['required', 'exists:categories,id'],
            'features' => 'nullable|array'
        ];

        // Lógica de imágenes (Crear vs Editar)
        if ($this->isMethod('post')) {
            $rules['image']   = 'required|array|min:1';
            $rules['image.*'] = 'image|mimes:jpeg,png,jpg|max:10240';
        }
        else {
            $rules['image']   = 'nullable|array';
            $rules['image.*'] = 'image|mimes:jpeg,png,jpg|max:10240';
        }


        if ($this->has('category_id')) {
            $category = Categories::find($this->category_id);

            if ($category) {
                // Si el nombre de tu categoría en la BD es "Apartamento"
                if ($category->name === 'Apartamento') {
                    $rules['features.pool']          = 'nullable|boolean';
                    $rules['features.bbq_zone']      = 'nullable|boolean';
                    $rules['features.balcony']       = 'nullable|boolean';
                    $rules['features.security_24_7'] = 'nullable|boolean';
                    $rules['features.gym']           = 'nullable|boolean';
                    $rules['features.parking']       = 'nullable|boolean';

                    // SOLUCIÓN: Nombre correcto "administration" y sin el límite tonto de max:100
                    $rules['features.administration'] = 'nullable|numeric|min:0';
                }

                // Si el nombre de tu categoría en la BD es "Casa"
                if ($category->name === 'Casa') {
                    $rules['features.patio']           = 'nullable|boolean';
                    $rules['features.terrace']         = 'nullable|boolean';
                    $rules['features.pool']            = 'nullable|boolean';
                    $rules['features.gated_community'] = 'nullable|boolean';

                    $rules['features.security_24_7']   = 'exclude_if:features.gated_community,false|boolean';
                }
            }
        }

        return $rules;
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

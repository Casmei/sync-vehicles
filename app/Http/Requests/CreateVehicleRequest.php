<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['nullable', 'string', 'max:50'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:150'],
            'version' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'array'],
            'year.model' => ['nullable', 'integer', 'between:1900,2100'],
            'year.build' => ['nullable', 'integer', 'between:1900,2100'],
            'optionals_json' => ['nullable', 'array'],
            'optionals_json.*' => ['string'],
            'fotos_json' => ['nullable', 'array'],
            'fotos_json.*' => ['url'],
            'doors' => ['nullable', 'integer', 'between:1,8'],
            'board' => ['nullable', 'string', 'max:10'],
            'chassi' => ['nullable', 'string', 'max:30'],
            'transmission' => ['nullable', 'string', 'max:50'],
            'km' => ['nullable', 'integer', 'min:0'],
            'price' => ['nullable', 'numeric'],
            'old_price' => ['nullable', 'numeric'],
            'color' => ['nullable', 'string', 'max:50'],
            'fuel' => ['nullable', 'string', 'max:50'],
            'sold' => ['nullable', 'boolean'],
            'category' => ['nullable', 'string', 'max:50'],
            'url_car' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}

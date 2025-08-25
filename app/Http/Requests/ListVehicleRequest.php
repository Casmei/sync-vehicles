<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantidade' => ['integer', 'min:1', 'max:100'],
            'pagina' => ['integer', 'min:1'],
        ];
    }
}

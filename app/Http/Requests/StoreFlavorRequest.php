<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFlavorRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string',
            'status' => 'sometimes|in:active,inactive',
            'recipes' => 'sometimes|array',
            'recipes.*.raw_material_id' => 'sometimes|exists:raw_materials,id',
            'recipes.*.quantity' => 'sometimes|integer|min:1'
        ];
    }
}

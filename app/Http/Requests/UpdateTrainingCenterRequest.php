<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTrainingCenterRequest extends FormRequest
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
        $param = $this->route('training_center') ?? $this->route('trainingcenter');
        $id = is_object($param) ? $param->id : $param;
        return [
            'nombre' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('training_centers', 'nombre')->ignore($id)],
            'codigo' => ['required', 'integer', 'min:0'],
            'department_id' => ['required', 'exists:departments,id'],
            'city_id' => ['required', 'exists:cities,id'],
        ];
    }
}

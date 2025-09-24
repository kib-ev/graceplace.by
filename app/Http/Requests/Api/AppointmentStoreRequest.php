<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AppointmentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'master_id' => 'required|exists:masters,id',
            'place_id' => 'required|exists:places,id',
            'client_name' => 'required|string|max:255',
            'client_phone' => 'required|string|max:20',
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'duration' => 'required|integer|min:30|max:480',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'errors' => $validator->errors()
        ], 422));
    }

    public function messages(): array
    {
        return [
            'master_id.required' => 'Необходимо выбрать мастера',
            'master_id.exists' => 'Выбранный мастер не существует',
            'place_id.required' => 'Необходимо выбрать место',
            'place_id.exists' => 'Выбранное место не существует',
            'client_name.required' => 'Необходимо указать имя клиента',
            'client_phone.required' => 'Необходимо указать телефон клиента',
            'start_time.required' => 'Необходимо указать время начала',
            'start_time.date_format' => 'Неверный формат времени начала',
            'duration.required' => 'Необходимо указать длительность',
            'duration.integer' => 'Длительность должна быть числом',
            'duration.min' => 'Минимальная длительность 30 минут',
            'duration.max' => 'Максимальная длительность 8 часов',
        ];
    }
} 
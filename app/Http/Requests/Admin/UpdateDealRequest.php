<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'value' => 'sometimes|required|numeric|min:0',
            'stage' => 'sometimes|required|string',
            'probability' => 'nullable|integer|min:0|max:100',
            'expected_close_date' => 'sometimes|required|date',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'dealable_type' => 'sometimes|required|string',
            'dealable_id' => 'sometimes|required|integer',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max per file
        ];
    }
}
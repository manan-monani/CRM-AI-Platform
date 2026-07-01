<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'dealable_type' => 'required|string',
            'dealable_id' => 'required|integer',
            'value' => 'required|numeric|min:0',
            'stage' => 'nullable|string',
            'probability' => 'nullable|integer|min:0|max:100',
            'expected_close_date' => 'required|date',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'required|file|max:10240', // 10MB
        ];
    }
}

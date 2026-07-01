<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'taskable_type' => 'nullable|string',
            'taskable_id' => 'nullable|integer',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'required|date',
            'assigned_to' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'required|file|max:10240', // 10MB
        ];
    }
}

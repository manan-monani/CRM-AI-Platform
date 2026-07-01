<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'activityable_type' => 'required|string',
            'activityable_id' => 'required|integer',
            'type' => 'required|string|in:call,email,meeting,note',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:0',
            'scheduled_at' => 'nullable|date',
            'outcome' => 'nullable|string',
        ];
    }
}

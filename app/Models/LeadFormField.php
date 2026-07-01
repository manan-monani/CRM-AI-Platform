<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadFormField extends BaseModel
{
    protected $fillable = [
        'lead_form_id',
        'label',
        'name',
        'type',
        'placeholder',
        'is_required',
        'options',
        'order_index',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'options' => 'array',
            'order_index' => 'integer',
        ];
    }

    public function leadForm(): BelongsTo
    {
        return $this->belongsTo(LeadForm::class);
    }
}

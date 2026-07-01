<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadForm extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'title',
        'description',
        'status',
        'branding_settings',
        'notification_emails',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'branding_settings' => 'array',
            'notification_emails' => 'array',
            'is_default' => 'boolean',
        ];
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(LeadFormField::class);
    }
}

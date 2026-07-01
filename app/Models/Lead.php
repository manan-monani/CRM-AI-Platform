<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends BaseModel
{
    protected $fillable = [
        'lead_form_id',
        'name',
        'email',
        'phone',
        'company',
        'message',
        'source',
        'status',
        'custom_data',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'converted_to_customer_id',
        'converted_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'converted_at' => 'datetime',
            'custom_data' => 'array',
        ];
    }

    public function leadSource(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class , 'source', 'slug');
    }

    public function leadForm(): BelongsTo
    {
        return $this->belongsTo(LeadForm::class);
    }

    /**
     * Get the customer this lead was converted to.
     */
    public function convertedCustomer(): BelongsTo
    {
        return $this->belongsTo(User::class , 'converted_to_customer_id');
    }

    /**
     * Check if lead has been converted to customer.
     */
    public function isConverted(): bool
    {
        return $this->status === 'converted' && $this->converted_to_customer_id !== null;
    }

    /**
     * Check if lead is still active (not converted or lost).
     */
    public function isActive(): bool
    {
        return !in_array($this->status, ['converted', 'lost']);
    }

    /**
     * Check if lead can be reconverted (converted but customer was deleted).
     */
    public function canBeReconverted(): bool
    {
        return $this->status === 'converted'
            && $this->converted_to_customer_id !== null
            && $this->convertedCustomer === null;
    }
}
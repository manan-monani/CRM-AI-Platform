<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BusinessSetting extends BaseModel
{
    protected $fillable = [
        'key',
        'value',
        'type',
    ];
    //
}

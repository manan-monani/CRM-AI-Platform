<?php

namespace Database\Seeders;

use App\Models\LeadSource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LeadSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = [
            'Landing Page',
            'Referral',
            'Social Media',
            'Direct',
            'Other',
        ];

        foreach ($sources as $source) {
            LeadSource::updateOrCreate(
                ['slug' => Str::slug($source)],
                ['name' => $source, 'is_active' => true]
            );
        }
    }
}

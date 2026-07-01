<?php

namespace Database\Seeders;

use App\Models\Lead;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    public function run(): void
    {
        // Create leads with different statuses
        Lead::factory()->count(5)->newLead()->create();
        Lead::factory()->count(3)->contacted()->create();
        Lead::factory()->count(2)->qualified()->create();
        Lead::factory()->count(1)->fromLandingPage()->create();
    }
}

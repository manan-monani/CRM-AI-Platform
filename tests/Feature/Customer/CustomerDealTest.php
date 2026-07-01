<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerDealTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_deal_with_attachments()
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $user = \App\Models\User::factory()->create(['type' => \App\Enums\UserType::CUSTOMER]);

        $file = \Illuminate\Http\UploadedFile::fake()->image('document.jpg');

        $response = $this->actingAs($user)
            ->post(route('customer.deals.store'), [
                'title' => 'New Deal Project',
                'value' => 5000,
                'probability' => 75,
                'description' => 'A test deal description.',
                'expected_close_date' => now()->addDays(10)->toDateString(),
                'attachments' => [$file],
            ]);

        $response->assertRedirect(route('customer.deals.index'));
        $this->assertDatabaseHas('deals', [
            'title' => 'New Deal Project',
            'value' => 5000,
            'dealable_id' => $user->id,
        ]);
    }
}

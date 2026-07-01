<?php

use App\Models\Lead;
use App\Models\LeadForm;
use App\Models\LeadFormField;

test('public lead form submission saves custom data', function () {
    // 1. Create a lead form with a custom field
    $form = LeadForm::create([
        'name' => 'Custom Field Test Form',
        'title' => 'Custom Field Test',
        'slug' => 'custom-field-test',
        'status' => 'active',
    ]);

    LeadFormField::create([
        'lead_form_id' => $form->id,
        'label' => 'Full Name',
        'name' => 'full-name',
        'type' => 'text',
        'is_required' => true,
        'order_index' => 1,
    ]);

    LeadFormField::create([
        'lead_form_id' => $form->id,
        'label' => 'Email Address',
        'name' => 'email',
        'type' => 'email',
        'is_required' => true,
        'order_index' => 2,
    ]);

    LeadFormField::create([
        'lead_form_id' => $form->id,
        'label' => 'My Secret Hobby',
        'name' => 'my-secret-hobby',
        'type' => 'text',
        'is_required' => true,
        'order_index' => 3,
    ]);

    // 2. Submit the form
    $response = $this->post("/form/{$form->slug}", [
        'full-name' => 'John Doe',
        'email' => 'john@example.com',
        'my-secret-hobby' => 'Knitting',
    ]);

    // 3. Verify response
    $response->assertSessionHas('success');

    // 4. Verify database
    $lead = Lead::where('email', 'john@example.com')->first();
    expect($lead)->not->toBeNull();
    expect($lead->name)->toBe('John Doe');
    expect($lead->custom_data)->toBe(['my-secret-hobby' => 'Knitting']);
});
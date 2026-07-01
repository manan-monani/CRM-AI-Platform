<?php

use App\Constants\Permissions;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    \Illuminate\Support\Facades\Gate::define(Permissions::LEAD_GENERATION_FORM_MANAGEMENT, function ($user) {
        return $user->hasPermission(Permissions::LEAD_GENERATION_FORM_MANAGEMENT);
    }
    );
});

test('admin guard works', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin, 'admin');
    expect(\Illuminate\Support\Facades\Auth::guard('admin')->check())->toBeTrue();
});

test('admin can create a lead form', function () {
    $admin = User::factory()->create(['type' => \App\Enums\UserType::ADMIN]);
    $role = Role::create(['name' => 'admin', 'guard_name' => 'admin']);
    $permission = \App\Models\Permission::create(['name' => Permissions::LEAD_GENERATION_FORM_MANAGEMENT]);

    $role->permissions()->attach($permission);
    $admin->roles()->attach($role);

    $response = $this->actingAs($admin, 'admin')->post(route('admin.lead-generation-form.store'), [
        'name' => 'Test Lead Form',
        'title' => 'Get a Quote',
        'description' => 'Fill out the form below',
        'status' => 'active',
        'branding_settings' => [
            'primary_color' => '#000000',
        ],
        'notification_emails' => ['admin@example.com'],
        'fields' => [
            [
                'label' => 'Full Name',
                'type' => 'text',
                'is_required' => true,
                'placeholder' => 'Enter your name',
            ],
        ],
    ]);

    $response->assertRedirect(route('admin.lead-generation-form.index'));
    $this->assertDatabaseHas('lead_forms', ['name' => 'Test Lead Form']);
    $this->assertDatabaseHas('lead_form_fields', ['label' => 'Full Name']);
});

test('admin can set a default lead form', function () {
    $admin = User::factory()->create(['type' => \App\Enums\UserType::ADMIN]);
    // Re-use or create new role/permissions if necessary, but cleaner to create fresh since tests reset DB
    $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'admin']);
    $permission = \App\Models\Permission::firstOrCreate(['name' => Permissions::LEAD_GENERATION_FORM_MANAGEMENT]);

    $role->permissions()->syncWithoutDetaching([$permission->id]);
    $admin->roles()->attach($role);

    // Create first form as default
    $response = $this->actingAs($admin, 'admin')->post(route('admin.lead-generation-form.store'), [
        'name' => 'Form 1',
        'title' => 'Form 1 Title',
        'status' => 'active',
        'is_default' => true,
        'fields' => [['label' => 'Name', 'type' => 'text']],
    ]);

    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('lead_forms', ['name' => 'Form 1', 'is_default' => true]);

    // Create second form as default
    $this->actingAs($admin, 'admin')->post(route('admin.lead-generation-form.store'), [
        'name' => 'Form 2',
        'title' => 'Form 2 Title',
        'status' => 'active',
        'is_default' => true,
        'fields' => [['label' => 'Name', 'type' => 'text']],
    ]);

    // Check that Form 1 is no longer default
    $this->assertDatabaseHas('lead_forms', ['name' => 'Form 2', 'is_default' => true]);
    $this->assertDatabaseHas('lead_forms', ['name' => 'Form 1', 'is_default' => false]);
});

test('admin can update a lead form', function () {
    $admin = User::factory()->create(['type' => \App\Enums\UserType::ADMIN]);
    $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'admin']);
    $permission = \App\Models\Permission::firstOrCreate(['name' => Permissions::LEAD_GENERATION_FORM_MANAGEMENT]);
    $role->permissions()->syncWithoutDetaching([$permission->id]);
    $admin->roles()->attach($role);

    $form = \App\Models\LeadForm::create([
        'name' => 'Old Name',
        'slug' => 'old-name',
        'status' => 'active',
    ]);
    $form->fields()->create([
        'label' => 'Old Field',
        'name' => 'old-field',
        'type' => 'text',
    ]);

    $response = $this->actingAs($admin, 'admin')->put(route('admin.lead-generation-form.update', $form->id), [
        'name' => 'New Name',
        'title' => 'New Title',
        'description' => 'New Description',
        'status' => 'active',
        'branding_settings' => ['primary_color' => '#ffffff'],
        'notification_emails' => ['new@example.com'],
        'fields' => [
            [
                'label' => 'New Field',
                'type' => 'email',
                'is_required' => true,
            ],
        ],
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('admin.lead-generation-form.index'));

    $this->assertDatabaseHas('lead_forms', [
        'id' => $form->id,
        'name' => 'New Name',
        'title' => 'New Title',
    ]);
    $this->assertDatabaseHas('lead_form_fields', [
        'lead_form_id' => $form->id,
        'label' => 'New Field',
    ]);
    $this->assertDatabaseMissing('lead_form_fields', ['label' => 'Old Field']);
});

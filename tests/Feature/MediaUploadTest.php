<?php

use App\Constants\Permissions;
use App\Enums\UserType;
use App\Models\Deal;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $role = Role::firstOrCreate(['name' => 'Admin', 'slug' => 'admin']);

    $permissions = [
        Permissions::DEAL_MANAGEMENT,
        Permissions::TASK_MANAGEMENT,
        Permissions::ACTIVITY_MANAGEMENT,
    ];

    foreach ($permissions as $permissionName) {
        $p = \App\Models\Permission::firstOrCreate(['name' => $permissionName]);
        $role->permissions()->syncWithoutDetaching([$p->id]);
    }

    $admin = User::factory()->create(['type' => UserType::ADMIN]);
    $admin->roles()->attach($role);
    $this->admin = $admin;
});

test('admin can upload media to a deal', function () {
    Storage::fake('public');

    $deal = Deal::factory()->create();
    $file = UploadedFile::fake()->image('document.jpg');

    $response = $this->actingAs($this->admin, 'admin')->post(route('admin.media.store'), [
        'file' => $file,
        'model_type' => Deal::class,
        'model_id' => $deal->id,
        'collection' => 'default',
    ]);

    $response->assertRedirect();
    $this->assertCount(1, $deal->getMedia('default'));
});

test('admin can create deal with attachments', function () {
    Storage::fake('public');

    $file1 = UploadedFile::fake()->image('doc1.jpg');
    $file2 = UploadedFile::fake()->image('doc2.jpg');

    $dealData = [
        'title' => 'New Deal with Files',
        'value' => 1000,
        'stage' => 'new',
        'dealable_type' => User::class,
        'dealable_id' => User::factory()->create(['type' => UserType::CUSTOMER])->id,
        'expected_close_date' => now()->addDays(30)->toDateString(),
        'attachments' => [$file1, $file2],
    ];

    $response = $this->actingAs($this->admin, 'admin')->post(route('admin.deals.store'), $dealData);

    $response->assertRedirect(route('admin.deals.index'));
    $deal = Deal::where('title', 'New Deal with Files')->first();
    $this->assertCount(2, $deal->getMedia('default'));
});

test('admin can create task with attachments', function () {
    Storage::fake('public');

    $file1 = UploadedFile::fake()->image('task1.jpg');

    $taskData = [
        'title' => 'New Task with Files',
        'due_date' => now()->addDays(1)->toDateTimeString(),
        'attachments' => [$file1],
    ];

    $response = $this->actingAs($this->admin, 'admin')->post(route('admin.tasks.store'), $taskData);

    $response->assertRedirect();
    $task = \App\Models\Task::where('title', 'New Task with Files')->first();
    $this->assertCount(1, $task->getMedia('default'));
});

test('admin can delete media', function () {
    Storage::fake('public');

    $deal = Deal::factory()->create();
    $file = UploadedFile::fake()->image('document.jpg');

    $deal->addMedia($file)->toMediaCollection('default');
    $media = $deal->getFirstMedia('default');

    $response = $this->actingAs($this->admin, 'admin')->delete(route('admin.media.destroy', $media));

    $response->assertRedirect();
    $this->assertCount(0, $deal->fresh()->getMedia('default'));
});

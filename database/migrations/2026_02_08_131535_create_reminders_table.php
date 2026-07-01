<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();

            // Polymorphic relationship - can be attached to deals, tasks, customers
            $table->morphs('remindable');

            // Reminder details
            $table->dateTime('remind_at');
            $table->enum('type', [
                'follow_up_call',
                'meeting',
                'proposal_submission',
                'closing_date',
                'task_due',
                'customer_follow_up',
                'custom',
            ])->default('custom');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->text('note')->nullable();

            // Status tracking
            $table->enum('status', ['pending', 'sent', 'dismissed', 'snoozed'])->default('pending');
            $table->dateTime('notified_at')->nullable();
            $table->dateTime('snoozed_until')->nullable();

            // Ownership
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['remind_at', 'status']);
            $table->index('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};

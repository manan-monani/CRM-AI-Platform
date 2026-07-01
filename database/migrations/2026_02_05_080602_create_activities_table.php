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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->morphs('activityable'); // lead, customer, deal
            $table->string('type'); // call, email, meeting, note
            $table->string('subject');
            $table->text('description')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('outcome')->nullable(); // busy, connected, left_message, etc.
            $table->foreignId('performed_by')->constrained('users')->cascadeOnDelete(); // Performed by
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};

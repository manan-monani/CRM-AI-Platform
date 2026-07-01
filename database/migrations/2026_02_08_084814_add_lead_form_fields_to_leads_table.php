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
        Schema::table('leads', function (Blueprint $table) {
            if (! Schema::hasColumn('leads', 'lead_form_id')) {
                $table->foreignId('lead_form_id')->nullable()->after('id')->constrained('lead_forms')->onDelete('set null');
            }
            if (! Schema::hasColumn('leads', 'source_url')) {
                $table->string('source_url')->nullable();
            }
            if (! Schema::hasColumn('leads', 'utm_source')) {
                $table->string('utm_source')->nullable();
            }
            if (! Schema::hasColumn('leads', 'utm_medium')) {
                $table->string('utm_medium')->nullable();
            }
            if (! Schema::hasColumn('leads', 'utm_campaign')) {
                $table->string('utm_campaign')->nullable();
            }
            // Keep existing custom_data if not explicitly removed by the instruction
            // The instruction only provided conditional additions, not removals of existing columns.
            // Assuming custom_data should remain as it wasn't part of the conditional block.
            if (! Schema::hasColumn('leads', 'custom_data')) {
                $table->json('custom_data')->nullable()->after('message'); // Store dynamic field values
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            //
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::table('leads')
            ->where('source', 'landing_page')
            ->update(['source' => 'landing-page']);

        \Illuminate\Support\Facades\DB::table('leads')
            ->where('source', 'social_media')
            ->update(['source' => 'social-media']);

        \Illuminate\Support\Facades\DB::table('leads')
            ->where('source', 'cold_call')
            ->update(['source' => 'cold-call']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('leads')
            ->where('source', 'landing-page')
            ->update(['source' => 'landing_page']);

        \Illuminate\Support\Facades\DB::table('leads')
            ->where('source', 'social-media')
            ->update(['source' => 'social_media']);

        \Illuminate\Support\Facades\DB::table('leads')
            ->where('source', 'cold-call')
            ->update(['source' => 'cold_call']);
    }
};

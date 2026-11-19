<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Families no longer pay: switch off family contact passes (kept for payment history). */
    public function up(): void
    {
        DB::table('plans')->where('audience', 'family')->update(['is_active' => false]);
    }

    public function down(): void
    {
        DB::table('plans')->where('audience', 'family')->update(['is_active' => true]);
    }
};

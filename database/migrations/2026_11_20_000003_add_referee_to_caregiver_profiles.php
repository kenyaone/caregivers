<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caregiver_profiles', function (Blueprint $table) {
            // Private: only the caregiver and admins see these (used when we phone the referee).
            $table->string('referee_name', 120)->nullable()->after('bio');
            $table->string('referee_phone', 20)->nullable()->after('referee_name');
            $table->string('referee_relationship', 60)->nullable()->after('referee_phone');
        });
    }

    public function down(): void
    {
        Schema::table('caregiver_profiles', fn (Blueprint $table) => $table->dropColumn(['referee_name', 'referee_phone', 'referee_relationship']));
    }
};

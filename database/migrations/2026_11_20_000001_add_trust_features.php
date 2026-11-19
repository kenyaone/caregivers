<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caregiver_profiles', function (Blueprint $table) {
            $table->date('good_conduct_issued_on')->nullable()->after('verification_checks');
            $table->timestamp('good_conduct_warned_at')->nullable()->after('good_conduct_issued_on');
            $table->timestamp('paused_at')->nullable()->after('available');
            $table->string('paused_reason')->nullable()->after('paused_at');
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('care_job_id')->nullable()->constrained('care_jobs')->nullOnDelete();
            $table->string('reason', 30);
            $table->text('details');
            $table->string('status', 20)->default('open')->index();
            $table->string('admin_note')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['reported_id', 'status']);
        });

        Schema::create('caregiver_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('skill', 40);
            $table->boolean('declared')->default(true);
            $table->timestamp('quiz_passed_at')->nullable();
            $table->unsignedTinyInteger('quiz_score')->nullable();
            $table->string('cert_status', 20)->nullable(); // pending, approved, rejected
            $table->foreignId('certificate_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->string('cert_note')->nullable();
            $table->timestamp('certified_at')->nullable();
            $table->foreignId('certified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'skill']);
        });

        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('skill', 40);
            $table->unsignedTinyInteger('score');
            $table->unsignedTinyInteger('total');
            $table->boolean('passed');
            $table->timestamps();
            $table->index(['user_id', 'skill']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('caregiver_skills');
        Schema::dropIfExists('reports');
        Schema::table('caregiver_profiles', fn (Blueprint $table) => $table->dropColumn(['good_conduct_issued_on', 'good_conduct_warned_at', 'paused_at', 'paused_reason']));
    }
};

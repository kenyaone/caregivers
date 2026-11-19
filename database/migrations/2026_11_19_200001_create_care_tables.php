<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('role', 20)->default('family')->after('phone')->index();
            $table->boolean('is_active')->default(true)->after('role');
        });

        Schema::create('caregiver_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('photo_path')->nullable();
            $table->string('headline', 120)->nullable();
            $table->json('care_types')->nullable();
            $table->json('arrangements')->nullable();
            $table->string('gender', 10)->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('county', 50)->nullable()->index();
            $table->string('town', 80)->nullable();
            $table->unsignedTinyInteger('years_experience')->nullable();
            $table->string('training')->nullable();
            $table->string('languages')->nullable();
            $table->unsignedInteger('expected_pay')->nullable();
            $table->string('pay_unit', 20)->default('per_month');
            $table->text('bio')->nullable();
            $table->boolean('available')->default(true);
            $table->string('verification_status', 20)->default('incomplete')->index();
            $table->string('verification_note')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('verification_checks')->nullable();
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->timestamps();
        });

        Schema::create('family_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('county', 50)->nullable();
            $table->string('town', 80)->nullable();
            $table->string('about', 1000)->nullable();
            $table->string('verification_status', 20)->default('incomplete');
            $table->string('verification_note')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('verification_checks')->nullable();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('path');
            $table->string('original_name');
            $table->timestamps();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('audience', 20)->index(); // caregiver | family
            $table->string('name');
            $table->unsignedInteger('price');
            $table->unsignedSmallInteger('duration_days');
            $table->unsignedSmallInteger('credits')->nullable(); // family contact unlocks
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained();
            $table->unsignedInteger('amount');
            $table->string('phone', 20)->nullable();
            $table->string('method', 20);
            $table->string('reference', 40)->nullable()->index();
            $table->string('provider_location')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->json('raw')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->index();
            $table->timestamps();
        });

        Schema::create('contact_passes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('credits_total');
            $table->unsignedSmallInteger('credits_used')->default(0);
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });

        Schema::create('unlocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('caregiver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('contact_pass_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->unique(['family_id', 'caregiver_id']);
        });

        Schema::create('care_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('care_type', 30)->index();
            $table->string('care_recipient');
            $table->string('arrangement', 20);
            $table->string('county', 50)->index();
            $table->string('town', 80);
            $table->date('start_date')->nullable();
            $table->unsignedInteger('pay_amount')->nullable();
            $table->string('pay_unit', 20)->default('per_month');
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->string('status', 20)->default('open')->index();
            $table->timestamps();
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('care_job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('caregiver_id')->constrained('users')->cascadeOnDelete();
            $table->string('message', 1000)->nullable();
            $table->string('status', 20)->default('applied');
            $table->timestamps();
            $table->unique(['care_job_id', 'caregiver_id']);
        });

        Schema::create('care_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('caregiver_id')->constrained('users')->cascadeOnDelete();
            $table->string('care_type', 30);
            $table->string('arrangement', 20);
            $table->date('start_date')->nullable();
            $table->string('message', 1000);
            $table->string('status', 20)->default('pending')->index();
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('caregiver_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('stars');
            $table->string('comment', 1000)->nullable();
            $table->timestamps();
            $table->unique(['family_id', 'caregiver_id']);
        });

        Schema::create('consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('version', 20);
            $table->text('statement');
            $table->string('context_type', 40)->nullable();
            $table->unsignedBigInteger('context_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'type', 'version']);
        });
    }

    public function down(): void
    {
        foreach (['consents', 'reviews', 'care_requests', 'job_applications', 'care_jobs', 'unlocks', 'contact_passes', 'subscriptions', 'payments', 'plans', 'documents', 'family_profiles', 'caregiver_profiles'] as $t) {
            Schema::dropIfExists($t);
        }
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['phone', 'role', 'is_active']));
    }
};

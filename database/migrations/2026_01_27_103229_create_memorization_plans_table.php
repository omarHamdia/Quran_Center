<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memorization_plans', function (Blueprint $table) {
            // حقول التقدم
            if (!Schema::hasColumn('memorization_plans', 'total_ayahs')) {
                $table->unsignedInteger('total_ayahs')->default(0)->after('progress_percentage');
            }
            if (!Schema::hasColumn('memorization_plans', 'completed_ayahs')) {
                $table->unsignedInteger('completed_ayahs')->default(0)->after('total_ayahs');
            }
            if (!Schema::hasColumn('memorization_plans', 'plan_status')) {
                $table->enum('plan_status', ['not_started', 'in_progress', 'completed', 'cancelled'])
                    ->default('not_started')
                    ->after('status');
            }
        });

        // تحديث جدول memorization_records إذا لزم الأمر
        Schema::table('memorization_records', function (Blueprint $table) {
            if (!Schema::hasColumn('memorization_records', 'deleted_at')) {
                $table->softDeletes();
            }
            if (!Schema::hasColumn('memorization_records', 'ayahs_count')) {
                $table->unsignedInteger('ayahs_count')->default(0)->after('to_ayah');
            }
            if (!Schema::hasColumn('memorization_records', 'evaluation')) {
                $table->enum('evaluation', ['excellent', 'very_good', 'good', 'acceptable', 'needs_review'])
                    ->nullable()
                    ->after('grade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('memorization_plans', function (Blueprint $table) {
            $table->dropColumn(['total_ayahs', 'completed_ayahs', 'plan_status']);
        });

        Schema::table('memorization_records', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['ayahs_count', 'evaluation']);
        });
    }
};
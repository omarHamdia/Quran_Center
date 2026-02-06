<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memorization_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('memorization_plans', 'total_ayahs')) {
                $table->unsignedInteger('total_ayahs')->default(0)->after('progress_percentage');
            }
            if (!Schema::hasColumn('memorization_plans', 'completed_ayahs')) {
                $table->unsignedInteger('completed_ayahs')->default(0)->after('total_ayahs');
            }
            if (!Schema::hasColumn('memorization_plans', 'plan_status')) {
                $table->string('plan_status', 20)->default('not_started')->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('memorization_plans', function (Blueprint $table) {
            $table->dropColumn(['total_ayahs', 'completed_ayahs', 'plan_status']);
        });
    }
};
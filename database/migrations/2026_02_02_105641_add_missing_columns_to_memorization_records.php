<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memorization_records', function (Blueprint $table) {
            if (!Schema::hasColumn('memorization_records', 'session_time')) {
                $table->time('session_time')->nullable()->after('session_date');
            }

            if (!Schema::hasColumn('memorization_records', 'duration_minutes')) {
                $table->unsignedInteger('duration_minutes')->nullable()->after('session_time');
            }

            if (!Schema::hasColumn('memorization_records', 'to_surah_id')) {
                $table->unsignedBigInteger('to_surah_id')->nullable()->after('surah_id');
            }

            if (!Schema::hasColumn('memorization_records', 'from_page')) {
                $table->unsignedInteger('from_page')->nullable()->after('to_ayah');
            }

            if (!Schema::hasColumn('memorization_records', 'to_page')) {
                $table->unsignedInteger('to_page')->nullable()->after('from_page');
            }

            if (!Schema::hasColumn('memorization_records', 'ayahs_count')) {
                $table->unsignedInteger('ayahs_count')->default(0)->after('to_page');
            }

            if (!Schema::hasColumn('memorization_records', 'evaluation')) {
                $table->string('evaluation', 30)->nullable()->after('ayahs_count');
            }

            if (!Schema::hasColumn('memorization_records', 'teacher_notes')) {
                $table->text('teacher_notes')->nullable();
            }

            if (!Schema::hasColumn('memorization_records', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('memorization_records', function (Blueprint $table) {
            $columns = ['session_time', 'duration_minutes', 'to_surah_id', 'from_page', 'to_page', 'ayahs_count', 'evaluation', 'teacher_notes', 'deleted_at'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('memorization_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
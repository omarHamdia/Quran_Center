<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memorization_records', function (Blueprint $table) {
            // تغيير session_type من enum إلى string
            $table->string('session_type', 20)->default('hifz')->change();
        });

        // إضافة to_surah_id إذا لم يكن موجودا��
        if (!Schema::hasColumn('memorization_records', 'to_surah_id')) {
            Schema::table('memorization_records', function (Blueprint $table) {
                $table->foreignId('to_surah_id')->nullable()->after('surah_id')->constrained('surahs');
            });
        }

        // إضافة status إذا لم يكن موجوداً أو تغييره
        Schema::table('memorization_records', function (Blueprint $table) {
            if (!Schema::hasColumn('memorization_records', 'status')) {
                $table->string('status', 20)->default('completed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('memorization_records', function (Blueprint $table) {
            $table->dropColumn('to_surah_id');
        });
    }
};
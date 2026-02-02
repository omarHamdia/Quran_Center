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
        Schema::table('memorization_plans', function (Blueprint $table) {
            // إضافة أعمدة الصفحات إذا لم تكن موجودة
            if (!Schema::hasColumn('memorization_plans', 'from_page')) {
                $table->unsignedInteger('from_page')->nullable()->after('to_surah_id');
            }
            
            if (!Schema::hasColumn('memorization_plans', 'to_page')) {
                $table->unsignedInteger('to_page')->nullable()->after('from_page');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memorization_plans', function (Blueprint $table) {
            $table->dropColumn(['from_page', 'to_page']);
        });
    }
};
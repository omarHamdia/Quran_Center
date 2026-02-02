<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surahs', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('number')->unique(); // 1-114
            $table->string('name_arabic', 50);
            $table->string('name_english', 50);
            $table->unsignedSmallInteger('total_ayahs');
            $table->enum('revelation_type', ['meccan', 'medinan']);
            $table->unsignedSmallInteger('page_start');
            $table->unsignedSmallInteger('page_end');
            $table->unsignedTinyInteger('juz_start');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surahs');
    }
};
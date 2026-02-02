<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memorization_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('surah_id')->constrained();
            $table->foreignId('memorization_plan_id')->nullable()->constrained()->nullOnDelete();
            
            $table->unsignedSmallInteger('from_ayah');
            $table->unsignedSmallInteger('to_ayah');
            $table->unsignedSmallInteger('ayahs_count');
            $table->date('session_date');
            
            $table->enum('session_type', ['new_memorization', 'revision', 'test'])->default('new_memorization');
            $table->enum('evaluation', ['excellent', 'very_good', 'good', 'acceptable', 'needs_review']);
            $table->unsignedTinyInteger('score')->nullable();
            $table->unsignedTinyInteger('mistakes_count')->default(0);
            $table->text('teacher_notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'session_date']);
            $table->index('evaluation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memorization_records');
    }
};
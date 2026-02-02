<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            
            // ولي الأمر
            $table->string('guardian_name', 100)->nullable();
            $table->string('guardian_phone', 20)->nullable();
            
            $table->date('enrollment_date')->nullable();
            $table->enum('current_level', ['beginner', 'elementary', 'intermediate', 'advanced', 'memorizer'])->default('beginner');
            $table->unsignedTinyInteger('memorized_juz')->default(0);
            $table->enum('status', ['active', 'inactive', 'graduated', 'withdrawn'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('teacher_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
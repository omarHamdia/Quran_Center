<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('specialty')->nullable(); // تجويد، حفظ، قراءات
            $table->string('qualification')->nullable(); // المؤهل
            $table->text('ijazah_details')->nullable(); // الإجازات
            $table->date('hire_date')->nullable();
            $table->unsignedTinyInteger('max_students')->default(30);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
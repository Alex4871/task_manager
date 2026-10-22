<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->string('title', 255);
            $table->text('description')->nullable();

            $table->timestampTz('due_date');
            $table->timestampTz('create_date')->useCurrent();
            $table->timestampTz('updated_at')->nullable();

            $table->string('status', 20)->default('Не выполнена');
            $table->string('priority', 20)->default('Средний');
            $table->string('category', 100)->nullable();

            $table->index('due_date');
            $table->index('create_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};

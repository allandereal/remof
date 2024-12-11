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
        Schema::create('transferables', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['File', 'Directory']);
            $table->text('hash')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('path', 500);
            $table->foreignId('transferable_id')
                ->constrained('transferables')
                ->cascadeOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transferables');
    }
};

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
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('hostname', 200)->nullable();
            $table->ipAddress('public_ip')->nullable();
            $table->ipAddress('private_ip')->nullable();
            $table->text('public_key')->nullable();
            $table->text('private_key')->nullable();
            $table->text('username')->nullable();
            $table->text('password')->nullable();
            $table->enum('os', ['Linux','Windows', 'Mac']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};

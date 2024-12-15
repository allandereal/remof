<?php

use App\Enums\TransferableType;
use App\Enums\TransferStatus;
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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->enum('type', TransferableType::values());
            $table->text('hash')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->foreignId('transfer_id')->nullable()->constrained('transfers')->cascadeOnDelete();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->enum('status', TransferStatus::values())->default(TransferStatus::PENDING->value);
            $table->foreignId('from_server_id')->constrained('servers')->cascadeOnDelete();
            $table->foreignId('to_server_id')->constrained('servers')->cascadeOnDelete();
            $table->text('from_path')->nullable();
            $table->text('to_path')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};

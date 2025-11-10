<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('queue_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_id')->constrained('queues')->cascadeOnDelete();
            $table->foreignId('counter_id')->constrained('counters')->cascadeOnDelete();
            $table->enum('status', ['start', 'called', 'served', 'done', 'canceled']);
            $table->timestamp('status_time')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('queue_logs');
    }
};

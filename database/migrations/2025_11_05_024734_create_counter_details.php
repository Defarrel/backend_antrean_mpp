<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('counter_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counter_id')->constrained('counters')->cascadeOnDelete();

            // Statistik harian
            $table->date('date'); 
            $table->integer('total_queues')->default(0);
            $table->integer('served')->default(0);
            $table->integer('called')->default(0);
            $table->integer('canceled')->default(0);
            $table->float('avg_duration')->default(0); 
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('counter_details');
    }
};

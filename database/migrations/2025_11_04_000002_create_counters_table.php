<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('counters', function (Blueprint $table) {
            $table->id();
            $table->string('counter_code')->unique(); 
            $table->string('name'); 
            $table->text('description')->nullable();
            $table->integer('quota')->default(0);
            $table->time('schedule_start')->nullable();
            $table->time('schedule_end')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('counters');
    }
};

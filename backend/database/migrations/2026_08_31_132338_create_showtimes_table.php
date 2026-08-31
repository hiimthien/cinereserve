<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('showtimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cinema_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->date('show_date');
            $table->string('start_time', 10); // '18:30'
            $table->string('end_time', 10);   // '21:16'
            $table->decimal('base_price', 10, 2)->default(12.00);
            $table->timestamps();

            $table->index(['movie_id', 'show_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('showtimes');
    }
};

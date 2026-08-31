<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('original_title')->nullable();
            $table->string('slug')->unique();
            $table->integer('duration'); // in minutes
            $table->date('release_date');
            $table->string('poster_url');
            $table->string('backdrop_url');
            $table->string('trailer_url')->nullable();
            $table->decimal('rating', 3, 1)->default(8.5);
            $table->json('genre')->nullable();
            $table->text('description')->nullable();
            $table->string('director')->nullable();
            $table->json('cast')->nullable();
            $table->string('status')->default('now_showing'); // now_showing, coming_soon, archived
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};

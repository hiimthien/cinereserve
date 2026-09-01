<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create snacks table
        if (!Schema::hasTable('snacks')) {
            Schema::create('snacks', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2);
                $table->string('image_url')->nullable();
                $table->string('badge')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Add combos JSON column to bookings table
        if (Schema::hasTable('bookings') && !Schema::hasColumn('bookings', 'combos')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->json('combos')->nullable()->after('total_amount');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('snacks');
        if (Schema::hasColumn('bookings', 'combos')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('combos');
            });
        }
    }
};

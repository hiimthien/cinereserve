<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('loyalty_rewards')) {
            Schema::create('loyalty_rewards', function (Blueprint $table) {
                $table->id();
                $table->string('reward_key')->unique();
                $table->string('title');
                $table->string('description');
                $table->integer('points_required');
                $table->decimal('discount_value', 12, 2);
                $table->enum('target', ['all', 'ticket', 'combo'])->default('all');
                $table->string('badge')->nullable();
                $table->string('icon')->default('gift');
                $table->string('prefix')->default('REDEEM-');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_rewards');
    }
};

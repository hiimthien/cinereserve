<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create vouchers table
        if (!Schema::hasTable('vouchers')) {
            Schema::create('vouchers', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('title');
                $table->string('description')->nullable();
                $table->enum('target', ['all', 'ticket', 'combo', 'fixed'])->default('all');
                $table->enum('discount_type', ['percent', 'fixed'])->default('fixed');
                $table->decimal('discount_value', 10, 2);
                $table->decimal('min_order_amount', 10, 2)->default(0);
                $table->decimal('max_discount_amount', 10, 2)->nullable();
                $table->integer('usage_limit')->default(1000);
                $table->integer('used_count')->default(0);
                $table->timestamp('expires_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Add voucher columns to bookings table
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('bookings', 'voucher_code')) {
                    $table->string('voucher_code')->nullable()->after('combos');
                }
                if (!Schema::hasColumn('bookings', 'discount_amount')) {
                    $table->decimal('discount_amount', 10, 2)->default(0)->after('voucher_code');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn(['voucher_code', 'discount_amount']);
            });
        }
    }
};

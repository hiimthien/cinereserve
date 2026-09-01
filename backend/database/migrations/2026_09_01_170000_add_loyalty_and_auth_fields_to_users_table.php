<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'points')) {
                $table->integer('points')->default(0)->after('avatar');
            }
            if (!Schema::hasColumn('users', 'membership_tier')) {
                $table->enum('membership_tier', ['member', 'vip', 'diamond'])->default('member')->after('points');
            }
            if (!Schema::hasColumn('users', 'total_spent')) {
                $table->decimal('total_spent', 12, 2)->default(0)->after('membership_tier');
            }
            if (!Schema::hasColumn('users', 'total_tickets_bought')) {
                $table->integer('total_tickets_bought')->default(0)->after('total_spent');
            }
            if (!Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->after('total_tickets_bought');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'avatar',
                'points',
                'membership_tier',
                'total_spent',
                'total_tickets_bought',
                'google_id',
            ]);
        });
    }
};

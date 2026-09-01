<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings') && !Schema::hasColumn('bookings', 'reminder_sent_at')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->timestamp('reminder_sent_at')->nullable()->after('checked_in_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'reminder_sent_at')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('reminder_sent_at');
            });
        }
    }
};

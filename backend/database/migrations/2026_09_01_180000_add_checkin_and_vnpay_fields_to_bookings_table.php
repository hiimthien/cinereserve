<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'check_in_status')) {
                $table->enum('check_in_status', ['pending', 'checked_in'])->default('pending')->after('status');
            }
            if (!Schema::hasColumn('bookings', 'checked_in_at')) {
                $table->timestamp('checked_in_at')->nullable()->after('check_in_status');
            }
            if (!Schema::hasColumn('bookings', 'checked_in_by')) {
                $table->string('checked_in_by')->nullable()->after('checked_in_at');
            }
            if (!Schema::hasColumn('bookings', 'vnp_transaction_no')) {
                $table->string('vnp_transaction_no')->nullable()->after('checked_in_by');
            }
            if (!Schema::hasColumn('bookings', 'vnp_bank_code')) {
                $table->string('vnp_bank_code')->nullable()->after('vnp_transaction_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'check_in_status',
                'checked_in_at',
                'checked_in_by',
                'vnp_transaction_no',
                'vnp_bank_code',
            ]);
        });
    }
};

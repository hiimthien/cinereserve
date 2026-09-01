<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `bookings` MODIFY COLUMN `check_in_status` VARCHAR(30) NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `bookings` MODIFY COLUMN `check_in_status` ENUM('pending', 'checked_in') NOT NULL DEFAULT 'pending'");
    }
};

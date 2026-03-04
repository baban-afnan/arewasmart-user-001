<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // For MySQL/MariaDB
            DB::statement("ALTER TABLE users CHANGE COLUMN role role ENUM('personal', 'agent', 'partner', 'business', 'staff', 'checker', 'super_admin', 'api') DEFAULT 'personal'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users CHANGE COLUMN role role ENUM('personal', 'agent', 'partner', 'business', 'staff', 'checker', 'super_admin') DEFAULT 'personal'");
        }
    }
};

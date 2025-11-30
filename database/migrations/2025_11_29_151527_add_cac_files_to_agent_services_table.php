<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('agent_services', function (Blueprint $table) {
            $table->string('cac_file')->nullable()->after('file_url');
            $table->string('memart_file')->nullable()->after('cac_file');
            $table->string('status_report_file')->nullable()->after('memart_file');
            $table->string('tin_file')->nullable()->after('status_report_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_services', function (Blueprint $table) {
            $table->dropColumn(['cac_file', 'memart_file', 'status_report_file', 'tin_file']);
        });
    }
};

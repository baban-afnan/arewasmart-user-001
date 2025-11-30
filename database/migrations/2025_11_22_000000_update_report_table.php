<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('report', function (Blueprint $table) {
            // Make existing fields nullable so we can use this table for other types of reports
            $table->string('account_number', 10)->nullable()->change();
            $table->string('account_name')->nullable()->change();
            $table->string('bank_code', 6)->nullable()->change();
            $table->string('bank_name')->nullable()->change();

            // Add new fields for Airtime/Data reports
            $table->string('phone_number')->nullable(); // Distinct from phone_no if needed, or just use this
            $table->string('network')->nullable();
            $table->string('ref')->nullable();
            $table->string('status')->default('pending');
            $table->string('type')->nullable(); // e.g., airtime, data
            $table->text('description')->nullable();
            $table->decimal('old_balance', 15, 2)->default(0);
            $table->decimal('new_balance', 15, 2)->default(0);
            $table->unsignedBigInteger('service_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('report', function (Blueprint $table) {
            // Revert changes (this might be tricky if data exists, but for now just drop new columns)
            $table->dropColumn([
                'phone_number',
                'network',
                'ref',
                'status',
                'type',
                'description',
                'old_balance',
                'new_balance',
                'service_id'
            ]);
            
            // We can't easily revert nullable->not nullable without data checks, so we skip that.
        });
    }
};

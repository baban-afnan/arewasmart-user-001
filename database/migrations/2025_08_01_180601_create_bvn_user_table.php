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
        Schema::create('bvn_user', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('service_field_id')->constrained('service_fields');
            $table->foreignId('service_id')->constrained('services');
            $table->string('bvn', 11);
            $table->string('agent_location');
            $table->string('bank_name');
            $table->string('account_no');
            $table->string('account_name');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->string('email') ->unique();
            $table->string('phone_no') ->unique();
            $table->string('address');
            $table->string('state');
            $table->string('lga');
            $table->date('dob');
            $table->enum('status', ['pending', 'processing', 'resolved', 'successful', 'rejetced', 'query'])->default('pending');
            $table->foreignId('transaction_id')->constrained();
            $table->dateTime('submission_date');
            $table->text('comment')->nullable();
            $table->string('agent_code')->nullable();
            $table->string('super_agent')->nullable();
            $table->string('performed_by', 150)->nullable();
            $table->string('approved_by', 150)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bvn_user');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('report', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('account_number', 10);
            $table->string('account_name');
            $table->string('bank_code', 6);
            $table->string('bank_name');
            $table->string('phone_no')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->decimal('amount', 15, 2)->default(0); 
            $table->timestamps();     
        });
    }

    public function down()
    {
        Schema::dropIfExists('report');
    }
};

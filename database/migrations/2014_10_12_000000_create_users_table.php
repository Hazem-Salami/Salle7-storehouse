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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('store_name');
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->tinyInteger('authenticated')->default(0);
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email')->unique();
            $table->string('phone_number');
            $table->tinyInteger('user_type')->default(0);
            $table->tinyInteger('is_active')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

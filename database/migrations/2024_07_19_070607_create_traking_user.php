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
        Schema::create('traking_user', function (Blueprint $table) {
            $table->id();
            $table->string('type', 255)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('password', 255)->nullable();
            $table->string('country', 255)->nullable();
            $table->string('organization_name', 255)->nullable();
            $table->string('organization_email', 255)->nullable();
            $table->string('google_id', 255)->nullable();
            $table->string('profile_image', 255)->nullable();
            $table->string('otp', 255)->nullable();
            $table->string('status_type', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('mobile', 10)->nullable(); 
            $table->string('remember_token')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traking_user');
    }
};

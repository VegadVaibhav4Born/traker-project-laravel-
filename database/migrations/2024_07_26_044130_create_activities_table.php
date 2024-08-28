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
        Schema::create('activities', function (Blueprint $table) {
           
            $table->id();
            $table->string('title', 255)->nullable();
            $table->string('project_id', 255)->nullable();
            $table->string('member_id', 255)->nullable();
            $table->string('mouse_click', 255)->nullable();
            $table->string('keyboard_click', 255)->nullable();
            $table->string('screenshot', 255)->nullable();
            $table->string('software_use_name', 255)->nullable();
            
            $table->string('durations', 255)->nullable();
            $table->timestamp('start_time')->useCurrent();
            $table->string('end_time', 255)->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};

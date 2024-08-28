<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimestampsToActivitiesTable extends Migration
{
    public function up()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->timestamps(); // Adds both `created_at` and `updated_at` columns
        });
    }

    public function down()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropTimestamps(); // Removes both `created_at` and `updated_at` columns
        });
    }
}

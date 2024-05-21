<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->string('type')->after('details'); // Adjust 'after' as necessary
        });
    }

    public function down()
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};

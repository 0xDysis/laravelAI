<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('threads')->nullable(); // Add threads column
            $table->json('assistant_ids')->nullable(); // Add assistant_ids column
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'threads')) {
                $table->dropColumn('threads');
            }
            if (Schema::hasColumn('users', 'assistant_ids')) {
                $table->dropColumn('assistant_ids');
            }
        });
    }
};    





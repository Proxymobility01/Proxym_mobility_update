<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('daily_distances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('validated_user_id')->constrained('validated_users')->onDelete('cascade');
            $table->date('date');
            $table->float('total_distance_km');
            $table->timestamps();

            $table->unique(['validated_user_id', 'date']); // one entry per user per day
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('daily_distances');
    }

};

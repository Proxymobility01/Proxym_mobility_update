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
        Schema::create('soe_batteries', function (Blueprint $table) {
            $table->id();
            $table->string('mac_id');
            $table->integer('soc')->default(100); // Toujours 100
            $table->float('soe'); // Valeur SYLA
            $table->date('date'); // Date du jour où SOC=100%
            $table->timestamp('first_seen')->nullable(); // Timestamp exact du moment
            $table->timestamps();

            $table->unique(['mac_id', 'date']); // Une seule entrée par batterie par jour
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soe_batteries');
    }
};

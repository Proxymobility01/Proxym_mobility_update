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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // Exemple : 'swap_alert', 'payment_reminder', etc.
            $table->unsignedBigInteger('user_id')->nullable(); // L'utilisateur concerné
            $table->text('message'); // Le contenu de la notification
            $table->boolean('is_read')->default(false); // Statut de lecture
            $table->timestamp('read_at')->nullable(); // Quand elle a été lue
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

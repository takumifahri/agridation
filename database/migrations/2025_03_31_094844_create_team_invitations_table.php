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
        Schema::create('team_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('team_lists')->onDelete('cascade');
            $table->string('sender_id'); // Match the data type of unique_id
            $table->foreign('sender_id')->references('unique_id')->on('users')->onDelete('cascade');
            $table->string('receiver_id'); // Match the data type of unique_id
            $table->foreign('receiver_id')->references('unique_id')->on('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'accept', 'reject'])->default('pending');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_invitations');
    }
};

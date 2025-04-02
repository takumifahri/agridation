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
        Schema::create('team_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name_team');
            $table->unsignedBigInteger('lomba_id');
            $table->json('anggota')->comment('Array of user_ids');
            $table->enum('status', ['pembayaran', 'lunas'])->default('pembayaran');
            $table->string('nama_pembimbing')->nullable();
            $table->string('no_pembimbing')->nullable();
            $table->string('peringkat')->nullable();
            $table->timestamp('update_at')->useCurrent();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_lists');
    }
};

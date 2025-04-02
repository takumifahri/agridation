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
        Schema::create('master_lombas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lomba');
            $table->text('deskripsi')->nullable();
            $table->string('link_gdrive')->nullable();
            $table->boolean('isAccepted')->default(false);
            $table->integer('total_peminat_tahun_lalu')->default(0);
            $table->integer('total_peminat_tahun_sekarang')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_lombas');
    }
};

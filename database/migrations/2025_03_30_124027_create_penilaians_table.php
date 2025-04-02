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
        Schema::create('penilaians', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('submission_id');
            $table->unsignedBigInteger('juri_id');
            $table->decimal('nilai', 5, 2)->default(0);
            $table->string('attachment_file')->nullable();
            $table->text('saran_comment')->nullable();
            $table->enum('status', ['rejected', 'accepted'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaians');
    }
};

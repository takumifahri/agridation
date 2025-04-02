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
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('team_id')->references('id')->on('team_lists')->onDelete('set null');
        });

        Schema::table('team_lists', function (Blueprint $table) {
            $table->foreign('lomba_id')->references('id')->on('master_lombas')->onDelete('cascade');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('team_id')->references('id')->on('team_lists')->onDelete('cascade');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->foreign('team_id')->references('id')->on('team_lists')->onDelete('cascade');
            $table->foreign('lomba_id')->references('id')->on('master_lombas')->onDelete('cascade');
        });

        Schema::table('penilaians', function (Blueprint $table) {
            $table->foreign('submission_id')->references('id')->on('submissions')->onDelete('cascade');
            $table->foreign('juri_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
        });

        Schema::table('team_lists', function (Blueprint $table) {
            $table->dropForeign(['lomba_id']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
        });

        Schema::table('submission', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropForeign(['lomba_id']);
        });

        Schema::table('penilaians', function (Blueprint $table) {
            $table->dropForeign(['submission_id']);
            $table->dropForeign(['juri_id']);
        });
    }
};

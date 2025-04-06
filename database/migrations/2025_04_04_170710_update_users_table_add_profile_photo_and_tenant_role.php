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
            //
            // Add profile_photo column
            $table->string('profile_photo')->nullable()->after('password');

            // Add isAgree column

            // Update the role column to include 'tenant'
            $table->enum('role', ['peserta', 'panitia', 'juri', 'tenant'])->default('peserta')->change();

            // Rename 'asal_instansi' column to 'asal_instansi'
            $table->renameColumn('asal_instansi', 'asal_instansi');

            // Add isAgree column
            $table->boolean('isAgree')->default(false)->after('asal_instansi');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            // Remove profile_photo column if it exists
            if (Schema::hasColumn('users', 'profile_photo')) {
                $table->dropColumn('profile_photo');
            }

            // Remove isAgree column
            if (Schema::hasColumn('users', 'isAgree')) {
                $table->dropColumn('isAgree');
            }

            // Revert the role column to its original state
            $table->enum('role', ['peserta', 'panitia', 'juri'])->default('peserta')->change();
        });
    }
};

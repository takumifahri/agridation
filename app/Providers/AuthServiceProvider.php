<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Penilaian;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
        $this->registerPolicies();

        // Define gates for roles
        Gate::define('admin', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('juri', function (User $user) {
            return $user->isJuri();
        });

        Gate::define('peserta', function (User $user) {
            return $user->isPeserta();
        });

        // Gates for specific abilities
        Gate::define('manage-users', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-teams', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-lomba', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-transactions', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('view-penilaian', function (User $user) {
            return $user->isAdmin() || $user->isJuri();
        });

        Gate::define('edit-penilaian', function (User $user, Penilaian $penilaian = null) {
            if (!$user->isJuri()) {
                return false;
            }
            
            if ($penilaian) {
                return $penilaian->juri_id === $user->id;
            }
            
            return true;
        });
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider; // ⬅️ Tambahkan ini!
use App\Models\User;
use App\Observers\UserObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        User::observe(UserObserver::class);
    }
}

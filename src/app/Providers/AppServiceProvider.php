<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Carbon::setLocale('ja');
        $now = Carbon::now()->translatedFormat('Y年n月j日 (D)' . "\n" . 'H:i');
        view()->share('nowDateTime', $now);
    }
}

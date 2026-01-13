<?php

namespace App\Providers;

use App\Services\ScheduleConflictService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ScheduleConflictService::class, function ($app) {
            return new ScheduleConflictService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        require_once app_path('Helpers/helpers.php');
        Carbon::setLocale('id');
        Date::setLocale('id');
        URL::defaults([
            'context' => session('context', 'pssk'),
        ]);
    }
}

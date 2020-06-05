<?php

namespace TimMcLeod\LaravelCoreLib\Providers;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use TimMcLeod\LaravelCoreLib\Calendar\VCalendar;

class LaravelCoreLibServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param ResponseFactory $factory
     */
    public function boot(ResponseFactory $factory)
    {
        // A macro for an ICS response.
        // Ex: Response::ics($calendar)
        $factory->macro('ics', function (VCalendar $calendar) use ($factory)
        {
            $filename = Str::slug($calendar->vEvents()->first()) . '.ics';
            $headers = [
                'Content-type'        => 'text/calendar; charset=utf-8',
                'Content-Disposition' => "attachment; filename=$filename"
            ];

            return $factory->make($calendar, 200, $headers);
        });

        // Publishes a config file to the project after running:
        // php artisan vendor:publish
        $this->publishes([
            __DIR__ . '/../../config/calendar.php' => config_path('calendar.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

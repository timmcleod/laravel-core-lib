<?php

namespace TimMcLeod\LaravelCoreLib\Providers;

use Illuminate\Foundation\Providers\ArtisanServiceProvider;
use TimMcLeod\LaravelCoreLib\Generators\ViewModelMakeCommand;

class LaravelCoreLibArtisanServiceProvider extends ArtisanServiceProvider
{
    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [];

    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $devCommands = [
        'ViewModelMake' => 'command.view-model.make',
    ];

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerViewModelMakeCommand()
    {
        $this->app->singleton('command.view-model.make', function ($app)
        {
            return new ViewModelMakeCommand($app['files']);
        });
    }
}

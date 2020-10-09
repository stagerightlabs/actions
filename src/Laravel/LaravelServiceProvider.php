<?php

namespace StageRightLabs\Actions\Laravel;

use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerFilesForPublication();
            $this->registerConsoleCommands();
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/actions.php',
            'actions'
        );
    }

    /**
     * Register the files that can be published.
     *
     * @return void
     */
    protected function registerFilesForPublication()
    {
        $this->publishes([
            __DIR__ . '/config/actions.php' => config_path('actions.php'),
        ], 'config');
    }

    public function registerConsoleCommands()
    {
        $this->commands([
            MakeAction::class,
            PublishStubs::class
        ]);
    }
}

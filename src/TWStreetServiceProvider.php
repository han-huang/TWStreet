<?php

namespace Hsquare\TWStreet;

use Illuminate\Support\ServiceProvider;
use Hsquare\TWStreet\Controllers\TWStreetController;
// use Hsquare\TWStreet\Commands\TWStreetCmd;
use Illuminate\Foundation\AliasLoader;
use Hsquare\TWStreet\Facades\TWStreet as TWStreetFacade;
use Colors\Color;

class TWStreetServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('twstreet', function () {
            return new TWStreetController(new Color());
        });

        if ($this->app->runningInConsole()) {
            $this->registerConsoleCommands();
        }

        $loader = AliasLoader::getInstance();
        $loader->alias('TWStreet', TWStreetFacade::class);
    }

    /**
     * Register the commands accessible from the Console.
     */
    private function registerConsoleCommands()
    {
        $this->commands(Commands\TWStreetCmd::class);
    }
}

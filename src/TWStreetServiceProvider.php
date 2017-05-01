<?php

namespace Hsquare\TWStreet;

use Illuminate\Support\ServiceProvider;
use Hsquare\TWStreet\Controllers\TWStreetController;
use Illuminate\Foundation\AliasLoader;
use Hsquare\TWStreet\Facades\TWStreet as TWStreetFacade;
use Colors\Color;
use Maatwebsite\Excel\ExcelServiceProvider;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;

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

        $this->app->register(ExcelServiceProvider::class);

        if ($this->app->runningInConsole()) {
            $this->registerConsoleCommands();
        }

        $loader = AliasLoader::getInstance();
        $loader->alias('TWStreet', TWStreetFacade::class);
        $loader->alias('Excel', ExcelFacade::class);
    }

    /**
     * Register the commands accessible from the Console.
     */
    private function registerConsoleCommands()
    {
        $this->commands(Commands\TWStreetCmd::class);
    }
}

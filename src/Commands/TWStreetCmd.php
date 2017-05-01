<?php

namespace Hsquare\TWStreet\Commands;

use Illuminate\Console\Command;
use Hsquare\TWStreet\Facades\TWStreet;

class TWStreetCmd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hsquare:twstreet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get data of counties, districts, and streets '.
                             'from postal web site of Taiwan and export excel file.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // echo "twstreet";
        TWStreet::getData();
    }
}

<?php

namespace Webdeveloperpcpl\Multilayer;

use Illuminate\Support\ServiceProvider;
use Webdeveloperpcpl\Multilayer\Console\Commands\MakeMultilayer;

class MultilayerServiceProvider extends ServiceProvider
{

    protected $defer = false;

    protected $commands = [
        MakeMultilayer::class,
    ];

    public function register()
    {
        $this->commands($this->commands);
    }


    public function provides()
    {
        $provides = $this->commands;

        return $provides;
    }

    public function boot()
    {
    }
}
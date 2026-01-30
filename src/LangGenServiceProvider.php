<?php

namespace AzizDevFull\LangGen;

use Illuminate\Support\ServiceProvider;
use AzizDevFull\LangGen\Commands\LangGenCommand;

class LangGenServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Config faylni birlashtirish
        $this->mergeConfigFrom(
            __DIR__ . '/../config/lang-gen.php',
            'lang-gen'
        );
    }

    public function boot()
    {
        // 1. Config faylni publish qilish imkoniyati
        $this->publishes([
            __DIR__ . '/../config/lang-gen.php' => config_path('lang-gen.php'),
        ], 'lang-gen-config');

        // 2. Commandni ro'yxatdan o'tkazish
        if ($this->app->runningInConsole()) {
            $this->commands([
                LangGenCommand::class,
            ]);
        }
    }
}
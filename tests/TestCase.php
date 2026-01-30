<?php

namespace AzizDevFull\LangGen\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use AzizDevFull\LangGen\LangGenServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            LangGenServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        // Configlarni shu yerda o'zgartirish mumkin
        $app['config']->set('lang-gen.default_lang', 'en');
        $app['config']->set('lang-gen.conflict_policy', 'overwrite');
    }

    /**
     * @inheritDoc
     */
    public function artisan($command, $parameters = [])
    {
        return parent::artisan($command, $parameters);
    }

    /**
     * @inheritDoc
     */
    public function be(\Illuminate\Contracts\Auth\Authenticatable $user, $guard = null)
    {
        return parent::be($user, $guard);
    }

    /**
     * @inheritDoc
     */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }

    /**
     * @inheritDoc
     */
    public function seed($class = 'Database\Seeders\DatabaseSeeder')
    {
        return parent::seed($class);
    }
}
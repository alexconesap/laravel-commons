<?php

namespace Tests;

use Alexconesap\Commons\Services\EnvConfigService;
use Alexconesap\Commons\Services\LocalizationService;
use Alexconesap\Commons\Services\SessionMemoryService;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;

class TestCase extends \Orchestra\Testbench\TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerBootstrapServices();
    }

    protected function getPackageProviders($app): array
    {
        return [];
    }

    protected function getPackageAliases($app): array
    {
        return [];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app->useEnvironmentPath(__DIR__ . '/../../');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);
        parent::getEnvironmentSetUp($app);
    }

    /**
     * Register services required by the helpers
     */
    private function registerBootstrapServices()
    {

        $this->app->singleton('yakuma.config', function () {
            return new EnvConfigService();
        });

        $this->app->singleton('yakuma.localization', function () {
            return new LocalizationService();
        });

        $this->app->singleton('yakuma.session', function () {
            return new SessionMemoryService();
        });
    }
}
<?php

namespace Modules\Tenancy\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Shared\Support\CurrentTenant;
use Nwidart\Modules\Support\ModuleServiceProvider;

class TenancyServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Tenancy';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'tenancy';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->singleton(CurrentTenant::class, fn () => new CurrentTenant);
    }

    public function boot(): void
    {
        parent::boot();

        $this->loadMigrationsFrom(module_path($this->name, '/database/migrations'));
    }

    /**
     * Define module schedules.
     *
     * @param  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}

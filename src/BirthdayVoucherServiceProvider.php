<?php

namespace Fmcpay\BirthdayVoucher;
use Illuminate\Support\ServiceProvider;
class BirthdayVoucherServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadSeedsFrom(__DIR__ . '/database/seeders');
        $this->loadRoutesFrom(__DIR__.'/routes/api.php');
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\GenerateBirthdayVouchersCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/birthday_voucher.php', 'birthday_voucher');
    }

    protected function loadSeedsFrom($path)
    {
        if (class_exists('Seeder')) {
            require_once $path . '/UserSeeder.php';
        }
    }


}

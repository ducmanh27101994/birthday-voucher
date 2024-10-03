<?php

namespace Fmcpay\BirthdayVoucher;
use Fmcpay\BirthdayVoucher\Http\Middleware\AdminMiddleware;
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
        $this->app['router']->aliasMiddleware('admin', AdminMiddleware::class);
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

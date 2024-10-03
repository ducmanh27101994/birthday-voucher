app/Console/Kernel.php 

$schedule->command('vouchers:generate')->dailyAt('07:00');


config/logging.php

    'channels' => [
        /.../
        'create_voucher_birthday' => [
            'driver' => 'single',
            'path' => storage_path('logs/create_voucher_birthday.log'),
            'level' => 'info',
        ],
        'use_voucher_birthday' => [
            'driver' => 'single',
            'path' => storage_path('logs/use_voucher_birthday.log'),
            'level' => 'info',
        ],
    ],

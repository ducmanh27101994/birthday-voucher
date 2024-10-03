<?php

namespace Fmcpay\BirthdayVoucher\Console;

use Fmcpay\BirthdayVoucher\Jobs\CreateBirthdayVouchers;
use Illuminate\Console\Command;
use Fmcpay\BirthdayVoucher\Models\User;
use Fmcpay\BirthdayVoucher\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GenerateBirthdayVouchersCommand extends Command
{
    protected $signature = 'vouchers:generate';
    protected $description = 'Tạo voucher cho người dùng';
    const CODE = 'birth_day'; //Code phân biệt giữa các voucher khác nhau
    const CURRENCY = 'VNĐ';

    public function handle()
    {
        $today = Carbon::now()->format('m-d');
        $limit = 100;

        try {
            User::whereRaw("DATE_FORMAT(birth_day, '%m-%d') = ?", [$today])
                ->chunk($limit, function ($users) {
                    foreach ($users as $user) {
                        CreateBirthdayVouchers::dispatch($user);
                    }
                });
            Log::channel('create_voucher_birthday')->info("Hoàn tất việc chạy voucher sinh nhật: " . Carbon::today());
        } catch (\Exception $e) {
            Log::channel('create_voucher_birthday')->error($e->getMessage());
        }
    }
}

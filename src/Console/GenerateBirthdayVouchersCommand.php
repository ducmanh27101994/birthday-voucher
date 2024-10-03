<?php

namespace Fmcpay\BirthdayVoucher\Console;

use Illuminate\Console\Command;
use Fmcpay\BirthdayVoucher\Models\User;
use Fmcpay\BirthdayVoucher\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GenerateBirthdayVouchersCommand extends Command
{
    protected $signature = 'vouchers:generate';
    protected $description = 'Tạo voucher cho người dùng';
    const CODE = 'birth_day'; //Code phân biệt giữa các voucher khác nhau

    public function handle()
    {
        $today = Carbon::now()->format('m-d');
        $users = User::whereRaw("DATE_FORMAT(birth_day, '%m-%d') = ?", [$today])->get();

        if (!empty($users)) {
            foreach ($users as $user) {
                $amount = config('birthday_voucher.amount', 100000);
                $expiresAt = Carbon::now()->addDays(config('birthday_voucher.expiry_days', 30));

                $voucherExists = Voucher::where('user_id', $user->id)
                    ->whereDate('created_at', Carbon::today())
                    ->where('code', GenerateBirthdayVouchersCommand::CODE)
                    ->exists();
                if (!$voucherExists) {
                    Voucher::create([
                        'user_id' => $user->id,
                        'amount' => $amount,
                        'expires_at' => $expiresAt,
                        'code' => GenerateBirthdayVouchersCommand::CODE
                    ]);
                    Log::channel('create_voucher_birthday')->info("Đã tạo voucher sinh nhật cho người dùng: {$user->name}");
                } else {
                    Log::channel('create_voucher_birthday')->warning("Người dùng {$user->name} đã nhận voucher sinh nhật trong ngày hôm nay.");
                }
            }
        }
        Log::channel('create_voucher_birthday')->info("Hoàn tất việc chạy voucher sinh nhật: " . Carbon::today());
    }
}

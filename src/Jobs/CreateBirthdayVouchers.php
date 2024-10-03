<?php

namespace Fmcpay\BirthdayVoucher\Jobs;

use Fmcpay\BirthdayVoucher\Console\GenerateBirthdayVouchersCommand;
use Fmcpay\BirthdayVoucher\Models\User;
use Fmcpay\BirthdayVoucher\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateBirthdayVouchers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle()
    {
        $amount = config('birthday_voucher.amount', 100000);
        $expiresAt = Carbon::now()->addDays(config('birthday_voucher.expiry_days', 30));
        $voucherExists = Voucher::where('user_id', $this->user->id)
            ->whereDate('created_at', Carbon::today())
            ->where('code', GenerateBirthdayVouchersCommand::CODE)
            ->exists();

        if (!$voucherExists) {
            Voucher::create([
                'id' => Str::uuid(),
                'user_id' => $this->user->id,
                'amount' => $amount,
                'expires_at' => $expiresAt,
                'code' => GenerateBirthdayVouchersCommand::CODE,
                'currency' => GenerateBirthdayVouchersCommand::CURRENCY,
            ]);
            Log::channel('create_voucher_birthday')->info("Đã tạo voucher sinh nhật cho người dùng: {$this->user->name}");
        } else {
            Log::channel('create_voucher_birthday')->warning("Người dùng {$this->user->name} đã nhận voucher sinh nhật trong ngày hôm nay.");
        }
    }
}

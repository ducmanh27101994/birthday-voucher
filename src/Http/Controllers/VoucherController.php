<?php

namespace Fmcpay\BirthdayVoucher\Http\Controllers;

use Fmcpay\BirthdayVoucher\Console\GenerateBirthdayVouchersCommand;
use Fmcpay\BirthdayVoucher\Models\Transaction;
use Illuminate\Http\Request;
use Fmcpay\BirthdayVoucher\Models\Voucher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VoucherController
{
    public function indexVoucherUser(Request $request)
    {
        $user_id = $request->input('id');
        if (!$user_id) {
            return response()->json([
                'message' => 'Chưa có id người dùng',
                'status' => 400,
            ]);
        }

        $vouchers = Voucher::where('user_id', $user_id)
            ->where('code', GenerateBirthdayVouchersCommand::CODE)
            ->get();

        return response()->json([
            'message' => 'Success',
            'status' => 200,
            'data' => $vouchers ?? []
        ]);
    }

    public function useVoucher(Request $request)
    {
        $voucher_id = $request->input('id');
        if (!$voucher_id) {
            return response()->json([
                'message' => 'Chưa có id voucher',
                'status' => 400,
            ]);
        }

        $voucher = Voucher::where('id', $voucher_id)
            ->where('code', GenerateBirthdayVouchersCommand::CODE)
            ->with('user')
            ->first();
        if ($voucher->is_used || $voucher->expires_at < now()) {
            return response()->json([
                'message' => 'Voucher đã được sử dụng hoặc hết hạn',
                'status' => 400,
            ]);
        }
        if (!$voucher->user || !$voucher->user->wallet) {
            return response()->json([
                'message' => 'Người dùng chưa tạo ví',
                'status' => 400,
            ]);
        }

        $this->useVoucherTransaction($voucher);

        return response()->json([
            'message' => 'Voucher used successfully',
            'status' => 200,
        ]);
    }

    private function useVoucherTransaction($voucher)
    {
        DB::transaction(function () use ($voucher) {
            $wallet = $voucher->user->wallet;
            $previousBalance = $wallet->balance;
            $wallet->currency = GenerateBirthdayVouchersCommand::CURRENCY;
            $wallet->balance += $voucher->amount;
            $wallet->save();

            $this->storeTransaction($voucher->user->id, $voucher->amount, $previousBalance, $wallet->balance, $wallet->currency, $voucher->code);

            $voucher->is_used = true;
            $voucher->save();
        });
    }

    private function storeTransaction($userId, $amount, $previousBalance, $newBalance, $currency, $voucherCode)
    {
        Transaction::create([
            'user_id' => $userId,
            'amount' => $amount,
            'previous_balance' => $previousBalance,
            'new_balance' => $newBalance,
            'currency' => $currency,
            'voucher_code' => $voucherCode,
            'created_at' => now(),
        ]);
    }
}
